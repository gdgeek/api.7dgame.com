<?php

namespace tests\unit\webmcp;

use api\modules\v1\services\AuthoringTaskStore as Store;
use api\modules\v1\services\ReliableWrite;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\web\User;
final class AuthoringTaskStoreTest extends TestCase
{
    private array $old;
    protected function setUp(): void
    {
        $this->old = ['db' => Yii::$app->db, 'user' => Yii::$app->has('user') ? Yii::$app->user : null];
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->set('user', new User(['identityClass' => TaskIdentity::class, 'enableSession' => false]));
        Yii::$app->user->switchIdentity(new TaskIdentity(7));
        require_once dirname(__DIR__, 3) . '/console/migrations/m260917_010000_add_webmcp_authoring_tasks.php';
        ob_start();
        $m = new \m260917_010000_add_webmcp_authoring_tasks(['db' => Yii::$app->db, 'compact' => true]);
        $m->safeUp();
        $m->safeUp();
        ob_end_clean();
    }
    protected function tearDown(): void
    {
        foreach ($this->old as $k => $v) {
            Yii::$app->set($k, $v);
        }
    }
    private function plan(): array
    {
        return [
            'taskId' => ReliableWrite::uuid(),
            'name' => '测试 🧪',
            'steps' => [
                [
                    'key' => 'createDraft',
                    'tool' => 'xrugc_stage_authoring_creation',
                    'input' => ['kind' => 'entity', 'name' => '实体'],
                ],
            ],
        ];
    }
    public function testRefreshReadsPlanProgressAndListingWithoutClientMemory(): void
    {
        $p = $this->plan();
        $a = Store::create($p);
        $id = $a['taskId'];
        $claim = ReliableWrite::uuid();
        $a = Store::change($id, ['revision' => 1, 'claimId' => $claim], 'claim');
        $progress = [
            'index' => 1,
            'status' => 'completed',
            'states' => [['key' => 'createDraft', 'status' => 'completed', 'result' => ['draftId' => ReliableWrite::uuid()]]],
        ];
        Store::change($id, ['revision' => $a['revision'], 'claimId' => $claim, 'progress' => $progress, 'release' => true], 'checkpoint');
        $read = Store::get($id);
        $this->assertEquals($progress, $read['progress']);
        $this->assertEquals($p['steps'], $read['plan']);
        $this->assertSame($id, Store::listing()['items'][0]['taskId']);
        $this->assertSame($read, Store::create($p));
    }
    public function testTwoClientsCannotClaimOrOverwriteTheSameProgress(): void
    {
        $p = $this->plan();
        $a = Store::create($p);
        $one = ReliableWrite::uuid();
        $two = ReliableWrite::uuid();
        $a = Store::change($a['taskId'], ['revision' => 1, 'claimId' => $one], 'claim');
        foreach ([['revision' => 1, 'claimId' => $two], ['revision' => $a['revision'], 'claimId' => $two]] as $body) {
            try {
                Store::change($a['taskId'], $body, 'claim');
                $this->fail('Concurrent claim accepted');
            } catch (\yii\web\ConflictHttpException) {
            }
        }
        $this->assertSame($a['revision'], Store::get($a['taskId'])['revision']);
    }
    public function testExpiredLeaseCannotCommitLateEvidence(): void
    {
        $a = Store::create($this->plan());
        $claim = ReliableWrite::uuid();
        $a = Store::change($a['taskId'], ['revision' => 1, 'claimId' => $claim], 'claim');
        Yii::$app->db->createCommand()->update(Store::TABLE, ['lease_until' => time() - 1], ['task_id' => $a['taskId']])->execute();
        $this->expectException(\yii\web\ConflictHttpException::class);
        Store::change($a['taskId'], ['revision' => $a['revision'], 'claimId' => $claim, 'progress' => $a['progress']], 'checkpoint');
    }
    public function testDifferentActorCannotReadOrClaimTask(): void
    {
        $a = Store::create($this->plan());
        Yii::$app->user->switchIdentity(new TaskIdentity(8));
        $this->assertSame([], Store::listing()['items']);
        $this->expectException(\yii\web\NotFoundHttpException::class);
        Store::get($a['taskId']);
    }
    public function testNewControllerRequiresJwtAndRbac(): void
    {
        $controller = new \api\modules\v1\controllers\AuthoringTaskController('authoring-task', Yii::$app);
        $b = $controller->behaviors();
        $this->assertSame(\yii\filters\auth\CompositeAuth::class, $b['authenticator']['class']);
        $this->assertSame(\bizley\jwt\JwtHttpBearerAuth::class, $b['authenticator']['authMethods'][0]['class']);
        $this->assertFalse($b['authenticator']['authMethods'][0]['throwException']);
        $this->assertLessThan(array_search('authenticator', array_keys($b)), array_search('corsFilter', array_keys($b)));
        $this->assertArrayHasKey('options', $controller->actions());
        $this->assertSame(\mdm\admin\components\AccessControl::class, $b['access']['class']);
    }
    public function testPreciseMigrationOnlySelectsTheAuthoringMigration(): void
    {
        $controller = new \console\controllers\WebMcpAuthoringMigrateController('web-mcp-authoring-migrate', Yii::$app);
        $method = new \ReflectionMethod($controller, 'getNewMigrations');
        $controller->db = Yii::$app->db;
        $this->assertSame(['m260917_010000_add_webmcp_authoring_tasks'], $method->invoke($controller));
    }
    public function testDifferentPlanWithSameIdConflicts(): void
    {
        $p = $this->plan();
        Store::create($p);
        $p['steps'][0]['input']['name'] = 'another';
        $this->expectException(\yii\web\ConflictHttpException::class);
        Store::create($p);
    }
    public function testDangerousToolCannotBePersisted(): void
    {
        $p = $this->plan();
        $p['steps'][0]['tool'] = 'xrugc_publish_scene';
        $this->expectException(\yii\web\BadRequestHttpException::class);
        Store::create($p);
    }
    public function testMismatchedStepKeysRollbackProgress(): void
    {
        $a = Store::create($this->plan());
        $claim = ReliableWrite::uuid();
        $a = Store::change($a['taskId'], ['revision' => 1, 'claimId' => $claim], 'claim');
        $bad = $a['progress'];
        $bad['states'][0]['key'] = 'other';
        try {
            Store::change($a['taskId'], ['revision' => $a['revision'], 'claimId' => $claim, 'progress' => $bad], 'checkpoint');
            $this->fail('Bad evidence accepted');
        } catch (\yii\web\BadRequestHttpException) {
        }
        $this->assertSame($a['progress'], Store::get($a['taskId'])['progress']);
    }
}
final class TaskIdentity implements \yii\web\IdentityInterface
{
    public function __construct(private int $id)
    {
    }
    public static function findIdentity($id)
    {
        return new self((int) $id);
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getAuthKey()
    {
        return '';
    }
    public function validateAuthKey($authKey)
    {
        return false;
    }
}
