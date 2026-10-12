<?php
namespace tests\integration;

use api\modules\v1\models\Verse;
use api\modules\v1\services\PublicationArchive;
use api\modules\v1\services\ReliableWrite;
use api\modules\v1\services\ScenePublication;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\User;

/** Destructive fixtures only in an explicitly named disposable webmcp_test_* DB. */
require_once __DIR__ . '/fixtures/PublicationFixture.php';

final class PublicationMySqlTest extends TestCase
{
    private Connection $db;
    private array $previous = [];
    private string $directory;
    private const TABLES = ['scene_publication_revision','webmcp_operation','snapshot','verse','verse_code','meta','meta_code','code','verse_meta','meta_resource','resource','file','manager','verse_space','space','user'];

    protected function setUp(): void
    {
        $dsn = getenv('WEBMCP_MYSQL_TEST_DSN');
        if (!$dsn || !function_exists('pcntl_fork')) $this->markTestSkipped('Requires isolated MySQL and pcntl');
        if (!preg_match('/(?:^|;)dbname=(webmcp_test_[a-z0-9_]+)(?:;|$)/D', $dsn)) throw new \RuntimeException('Unsafe database');
        foreach (['db','request','user'] as $key) $this->previous[$key] = Yii::$app->get($key);
        $applicationConfig = require dirname(__DIR__, 3) . '/files/common/config/main-local.php';
        $this->db = new Connection(['dsn' => $dsn, 'username' => getenv('WEBMCP_MYSQL_TEST_USER') ?: 'root', 'password' => getenv('WEBMCP_MYSQL_TEST_PASSWORD') ?: '', 'charset' => $applicationConfig['components']['db']['charset']]);
        Yii::$app->set('db', $this->db);
        Yii::$app->set('request', new Request(['cookieValidationKey'=>'test','scriptUrl'=>'','hostInfo'=>'http://localhost']));
        Yii::$app->set('user', new User(['identityClass'=>PublicationIdentity::class,'enableSession'=>false]));
        Yii::$app->user->switchIdentity(new PublicationIdentity());
        \tests\integration\fixtures\PublicationFixture::reset($this->db);
        $this->directory = sys_get_temp_dir().'/p2-mysql-'.bin2hex(random_bytes(6)); mkdir($this->directory,0700);
    }
    protected function tearDown(): void
    {
        if (isset($this->db)) $this->db->close();
        foreach ($this->previous as $key=>$value) Yii::$app->set($key,$value);
        if (isset($this->directory)) { foreach (glob($this->directory.'/*') as $path) unlink($path); rmdir($this->directory); }
    }
    private function publish(string $operation, string $revision, ?callable $beforeCapture = null): array
    {
        Yii::$app->request->headers->set('Idempotency-Key',$operation);
        Yii::$app->request->headers->set('If-Match','"'.$revision.'"');
        return ReliableWrite::run('verse',1,'publish',['_publicationLanguage'=>'lua'],fn()=>null,function($verse) use($beforeCapture) {
            if ($beforeCapture) $beforeCapture();
            return ScenePublication::publish($verse);
        });
    }
    private function awaitFile(string $name): void
    {
        $end=microtime(true)+10;
        while (!file_exists($this->directory.'/'.$name) && microtime(true)<$end) usleep(10000);
        if (!file_exists($this->directory.'/'.$name)) throw new \RuntimeException('Barrier timeout: '.$name);
    }
    public function testSharedDependenciesUseOneReadViewAndNeverPersistUrlsOrHydration(): void
    {
        $revision=Verse::findOne(1)->serverRevision;
        $this->db->close();
        $pid=pcntl_fork();
        if ($pid===0) {
            try {
                $this->awaitFile('capturing');
                $tx=$this->db->beginTransaction();
                $this->db->createCommand()->update('meta',['title'=>'new'],['id'=>2])->execute();
                $this->db->createCommand()->update('meta_code',['lua'=>'new meta'],['id'=>2])->execute();
                $this->db->createCommand()->update('resource',['name'=>'new resource'],['id'=>4])->execute();
                $tx->commit(); touch($this->directory.'/mutated');
            } catch (\Throwable $e) { file_put_contents($this->directory.'/error', $e->getMessage()); }
            exit(0);
        }
        $result=$this->publish(ReliableWrite::uuid(),$revision,function() {
            touch($this->directory.'/capturing'); $this->awaitFile('mutated');
        });
        pcntl_waitpid($pid,$status);
        $archive=PublicationArchive::read(1,$result['publicationVersionId'],fn()=>null);
        $body=json_decode($archive['canonicalBody'],true);
        $this->assertSame('old',$body['runtime']['metas'][0]['title']);
        $this->assertSame("local meta = {}\nlocal index = ''\nold meta",$body['runtime']['metas'][0]['code']);
        $this->assertSame('old resource',$body['runtime']['resources'][0]['name']);
        $this->assertStringNotContainsString('signature',$archive['canonicalBody']);
        $this->assertNull((new Query())->from('resource')->select('uuid')->scalar());
        $this->assertSame('new',(new Query())->from('meta')->select('title')->scalar());
        $snapshot=(new Query())->from('snapshot')->one();
        $this->assertEquals($body['runtime']['metas'],json_decode($snapshot['metas'],true));
        $this->assertSame($result['contentHash'],$result['writeReceipt']['contentHash']);
    }
    public function testParallelSameOperationCreatesExactlyOneArchive(): void { $this->racePublications(true); }

    public function testApplicationConnectionPreservesFourBytePublicationCharacters(): void
    {
        $script = 'print("发布 🚀 𠮷")';
        $this->db->createCommand()->update('verse_code', ['lua' => $script], ['id' => 1])->execute();
        $result = $this->publish(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $archive = PublicationArchive::read(1, $result['publicationVersionId'], fn () => null);
        $this->assertStringContainsString('🚀 𠮷', $archive['canonicalBody']);
        $this->assertSame($result['contentHash'], $archive['contentHash']);
        $this->assertSame("local verse = {}\n local is_playing = false\n" . $script,
            (new Query())->from('snapshot')->select('code')->scalar());
    }
    public function testParallelDifferentPublicationsKeepBothIndependentArchives(): void { $this->racePublications(false); }
    public function testParallelPublicationsRetainExactlyTwentyBodies(): void { $this->racePublications(false, 20); }
    public function testParallelRetryExpiresOnlyOneOldBody(): void { $this->racePublications(true, 20); }
    private function racePublications(bool $sameKey, int $seed = 0): void
    {
        $revision=Verse::findOne(1)->serverRevision;
        for ($i = 0; $i < $seed; $i++) $this->publish(ReliableWrite::uuid(), $revision);
        $operation=ReliableWrite::uuid(); $this->db->close();
        $children=[];
        for ($i=0;$i<2;$i++) {
            $pid=pcntl_fork();
            if ($pid===0) {
                try {
                    touch($this->directory.'/ready-'.$i); $this->awaitFile('go');
                    $result=$this->publish($sameKey ? $operation : ReliableWrite::uuid(),$revision,fn()=>usleep(150000));
                    file_put_contents($this->directory.'/result-'.$i,json_encode($result));
                } catch (\Throwable $e) { file_put_contents($this->directory.'/result-'.$i,json_encode(['error'=>$e->getMessage()])); }
                exit(0);
            }
            $children[]=$pid;
        }
        $this->awaitFile('ready-0'); $this->awaitFile('ready-1'); touch($this->directory.'/go');
        foreach ($children as $pid) pcntl_waitpid($pid,$status);
        $rows=array_map(fn($path)=>json_decode(file_get_contents($path),true),glob($this->directory.'/result-*'));
        $this->assertCount(2,$rows);
        foreach ($rows as $row) $this->assertArrayNotHasKey('error',$row,json_encode($row));
        if ($sameKey) $this->assertSame($rows[0]['publicationVersionId'],$rows[1]['publicationVersionId']);
        else $this->assertNotSame($rows[0]['publicationVersionId'],$rows[1]['publicationVersionId']);
        $this->assertSame($rows[0]['contentHash'],$rows[1]['contentHash']);
        $this->assertSame($seed + ($sameKey ? 1 : 2),(int)(new Query())->from('scene_publication_revision')->count());
        if ($seed) {
            $this->assertSame(20, PublicationArchive::listing(1, fn () => null)['total']);
            $this->assertSame($sameKey ? 1 : 2, (int) (new Query())->from('scene_publication_revision')->where(['canonical_body' => '', 'byte_length' => 0])->count());
            foreach ($rows as $row) $this->assertSame('verified', PublicationArchive::read(1, $row['publicationVersionId'], fn () => null)['integrity']);
        }
        $this->assertSame($seed + ($sameKey ? 1 : 2),(int)(new Query())->from('webmcp_operation')->count());
        $this->assertSame(1,(int)(new Query())->from('snapshot')->count());
    }
    public function testFailedReceiptRollsBackExistingSnapshotAndArchiveInMysql(): void
    {
        $revision=Verse::findOne(1)->serverRevision;
        for ($i = 0; $i < 20; $i++) $this->publish(ReliableWrite::uuid(), $revision);
        $archives = (new Query())->from('scene_publication_revision')->orderBy('id')->all();
        $before=(new Query())->from('snapshot')->one();
        $this->db->createCommand()->update('verse_code',['lua'=>'later'],['id'=>1])->execute();
        $rejectedOperation = ReliableWrite::uuid();
        $this->db->createCommand('ALTER TABLE webmcp_operation ADD CONSTRAINT reject_receipt CHECK (operation_id <> ' . $this->db->quoteValue($rejectedOperation) . ')')->execute();
        try { $this->publish($rejectedOperation,Verse::findOne(1)->serverRevision); $this->fail('Expected receipt failure'); }
        catch (\yii\db\Exception) {
            $this->assertSame($before,(new Query())->from('snapshot')->one());
            $this->assertSame($archives, (new Query())->from('scene_publication_revision')->orderBy('id')->all());
            $this->assertSame(20,(int)(new Query())->from('webmcp_operation')->count());
        }
    }
    public function testSaveWaitsForPublicationOwnerLock(): void
    {
        $revision=Verse::findOne(1)->serverRevision; $this->db->close();
        $pid=pcntl_fork();
        if ($pid===0) {
            try {
                $this->awaitFile('publish-locked'); touch($this->directory.'/save-started');
                Yii::$app->request->headers->set('Idempotency-Key', ReliableWrite::uuid());
                Yii::$app->request->headers->set('If-Match', '"'.$revision.'"');
                ReliableWrite::run('verse',1,'save',['name'=>'saved later'],fn()=>null,function() {
                    $this->db->createCommand()->update('verse',['name'=>'saved later'],['id'=>1])->execute();
                    return ['id'=>1];
                });
            } catch (\Throwable $e) { file_put_contents($this->directory.'/error',$e->getMessage()); }
            exit(0);
        }
        $result=$this->publish(ReliableWrite::uuid(),$revision,function() {
            touch($this->directory.'/publish-locked'); $this->awaitFile('save-started'); usleep(150000);
        });
        pcntl_waitpid($pid,$status);
        $this->assertFileDoesNotExist($this->directory.'/error');
        $body=json_decode(PublicationArchive::read(1,$result['publicationVersionId'],fn()=>null)['canonicalBody'],true);
        $this->assertSame('A',$body['scene']['name']);
        $this->assertSame('saved later',Verse::findOne(1)->name);
    }
    public function testNonTransactionalDependenciesFailClosed(): void
    {
        $this->db->createCommand('ALTER TABLE resource ENGINE=MyISAM')->execute();
        $this->expectExceptionMessage('publication_storage_not_ready');
        $this->publish(ReliableWrite::uuid(),Verse::findOne(1)->serverRevision);
    }
}
final class PublicationIdentity implements IdentityInterface
{
 public static function findIdentity($id): ?self{return new self();}
 public static function findIdentityByAccessToken($token,$type=null): ?self{return null;}
 public function getId(): int{return 7;}
 public function getAuthKey(): string{return '';}
 public function validateAuthKey($authKey): bool{return false;}
}
