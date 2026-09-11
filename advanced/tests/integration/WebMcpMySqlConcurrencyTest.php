<?php

namespace tests\integration;

use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\User;

/** Runs only against an explicitly named disposable webmcp_test_* database. */
final class WebMcpMySqlConcurrencyTest extends TestCase
{
    private Connection $db;
    private array $previous = [];
    private string $directory;

    protected function setUp(): void
    {
        $dsn = getenv('WEBMCP_MYSQL_TEST_DSN');
        if (!$dsn || !function_exists('pcntl_fork')) {
            $this->markTestSkipped('Requires WEBMCP_MYSQL_TEST_DSN and pcntl');
        }
        if (!preg_match('/(?:^|;)dbname=(webmcp_test_[a-z0-9_]+)(?:;|$)/D', $dsn)) {
            throw new \RuntimeException('Refusing to use a database outside webmcp_test_*');
        }
        foreach (['db', 'user', 'request'] as $name) {
            $this->previous[$name] = Yii::$app->has($name) ? Yii::$app->get($name) : null;
        }
        $this->db = new Connection(['dsn' => $dsn, 'username' => getenv('WEBMCP_MYSQL_TEST_USER') ?: 'root', 'password' => getenv('WEBMCP_MYSQL_TEST_PASSWORD') ?: '', 'charset' => 'utf8mb4']);
        Yii::$app->set('db', $this->db);
        Yii::$app->set('request', new Request(['cookieValidationKey' => 'test', 'scriptUrl' => '', 'hostInfo' => 'http://localhost']));
        Yii::$app->set('user', new User(['identityClass' => MySqlReceiptIdentity::class, 'enableSession' => false, 'loginUrl' => null]));
        Yii::$app->user->switchIdentity(new MySqlReceiptIdentity());
        foreach (['verse', 'verse_code', 'webmcp_operation', 'scene_publication_revision'] as $table) {
            $this->db->createCommand("DROP TABLE IF EXISTS $table")->execute();
        }
        $this->db->createCommand('CREATE TABLE verse (id INT PRIMARY KEY, author_id INT, name TEXT, data JSON) ENGINE=InnoDB')->execute();
        $this->db->createCommand('CREATE TABLE verse_code (id INT PRIMARY KEY, verse_id INT, code_id INT NULL, blockly TEXT, js TEXT, lua TEXT) ENGINE=InnoDB')->execute();
        $this->db->createCommand()->insert('verse', ['id' => 1, 'author_id' => 7, 'name' => 'original', 'data' => '{}'])->execute();
        require_once dirname(__DIR__, 2) . '/console/migrations/m260911_230000_add_webmcp_write_receipts.php';
        $migration = new \m260911_230000_add_webmcp_write_receipts(['db' => $this->db, 'compact' => true]);
        ob_start();
        $migration->safeUp();
        $migration->safeUp();
        ob_end_clean();
        $this->directory = sys_get_temp_dir() . '/webmcp-race-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        foreach ($this->previous as $name => $component) Yii::$app->set($name, $component);
        if (isset($this->db)) $this->db->close();
        if (isset($this->directory)) {
            foreach (glob($this->directory . '/*') as $path) unlink($path);
            rmdir($this->directory);
        }
    }

    private function race(bool $sameKey): array
    {
        $revision = Verse::findOne(1)->serverRevision;
        $operation = ReliableWrite::uuid();
        $this->db->close(); // Children must establish independent PDO connections.
        $children = [];
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            if ($pid === -1) throw new \RuntimeException('fork failed');
            if ($pid === 0) {
                touch($this->directory . '/ready-' . $i);
                $deadline = microtime(true) + 10;
                while (!file_exists($this->directory . '/go') && microtime(true) < $deadline) usleep(10000);
                try {
                    Yii::$app->request->headers->set('Idempotency-Key', $sameKey ? $operation : ReliableWrite::uuid());
                    Yii::$app->request->headers->set('If-Match', '"' . $revision . '"');
                    $result = ReliableWrite::run('verse', 1, 'save', ['name' => 'changed'], fn () => null, function () {
                        usleep(250000); // Make the second request compete for the same row lock.
                        $this->db->createCommand()->update('verse', ['name' => 'changed'], ['id' => 1])->execute();
                        return ['id' => 1];
                    });
                    file_put_contents($this->directory . '/result-' . $i, json_encode(['status' => 'completed', 'replayed' => $result['replayed'] ?? false]));
                } catch (\Throwable $error) {
                    file_put_contents($this->directory . '/result-' . $i, json_encode(['status' => $error instanceof \yii\web\ConflictHttpException ? 'conflict' : 'error', 'error' => get_class($error) . ': ' . $error->getMessage()]));
                }
                exit(0);
            }
            $children[] = $pid;
        }
        $deadline = microtime(true) + 10;
        while (count(glob($this->directory . '/ready-*')) < 2 && microtime(true) < $deadline) usleep(10000);
        touch($this->directory . '/go');
        foreach ($children as $pid) pcntl_waitpid($pid, $status);
        return array_map(fn ($path) => json_decode(file_get_contents($path), true), glob($this->directory . '/result-*'));
    }

    public function testParallelSameOperationCommitsExactlyOnce(): void
    {
        $results = $this->race(true);
        $this->assertCount(2, $results);
        $this->assertSame(['completed', 'completed'], array_column($results, 'status'), json_encode($results));
        $this->assertSame(1, count(array_filter($results, fn ($row) => $row['replayed'])));
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }

    public function testParallelDifferentOperationsRejectTheStaleWriter(): void
    {
        $results = $this->race(false);
        $statuses = array_column($results, 'status');
        sort($statuses);
        $this->assertSame(['completed', 'conflict'], $statuses, json_encode($results));
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }
}

final class MySqlReceiptIdentity implements IdentityInterface
{
    public static function findIdentity($id): ?self { return new self(); }
    public static function findIdentityByAccessToken($token, $type = null): ?self { return null; }
    public function getId(): int { return 7; }
    public function getAuthKey(): string { return ''; }
    public function validateAuthKey($authKey): bool { return false; }
}
