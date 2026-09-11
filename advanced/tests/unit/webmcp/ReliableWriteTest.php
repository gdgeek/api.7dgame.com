<?php

namespace tests\unit\webmcp;

use api\modules\v1\controllers\MetaController;
use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\User;

final class ReliableWriteTest extends TestCase
{
    private array $components;
    private Connection $db;

    protected function setUp(): void
    {
        $this->components = [];
        foreach (['db', 'user', 'request'] as $name) {
            $this->components[$name] = Yii::$app->has($name) ? Yii::$app->get($name) : null;
        }
        $this->db = new Connection(['dsn' => 'sqlite::memory:', 'pdoClass' => class_exists('Pdo\\Sqlite') ? 'Pdo\\Sqlite' : 'PDO']);
        $this->db->open();
        $createFunction = method_exists($this->db->pdo, 'createFunction') ? 'createFunction' : 'sqliteCreateFunction';
        $this->db->pdo->$createFunction('NOW', fn () => '2026-09-11 23:00:00');
        Yii::$app->set('db', $this->db);
        Yii::$app->set('request', new Request(['cookieValidationKey' => 'test', 'scriptUrl' => '', 'hostInfo' => 'http://localhost']));
        Yii::$app->set('user', new User(['identityClass' => ReceiptIdentity::class, 'enableSession' => false, 'loginUrl' => null]));
        Yii::$app->user->switchIdentity(new ReceiptIdentity(7));
        foreach ([
            'user' => 'id INTEGER PRIMARY KEY',
            'file' => 'id INTEGER PRIMARY KEY',
            'verse' => 'id INTEGER PRIMARY KEY, author_id INTEGER, updater_id INTEGER, name TEXT, uuid TEXT, data TEXT, info TEXT, description TEXT, image_id INTEGER, created_at TEXT, updated_at TEXT',
            'meta' => 'id INTEGER PRIMARY KEY, author_id INTEGER, updater_id INTEGER, title TEXT, uuid TEXT, prefab INTEGER DEFAULT 0, data TEXT, info TEXT, events TEXT, image_id INTEGER, created_at TEXT, updated_at TEXT',
            'verse_code' => 'id INTEGER PRIMARY KEY, verse_id INTEGER UNIQUE, code_id INTEGER, blockly TEXT, js TEXT, lua TEXT',
            'meta_code' => 'id INTEGER PRIMARY KEY, meta_id INTEGER UNIQUE, code_id INTEGER, blockly TEXT, js TEXT, lua TEXT',
            'code' => 'id INTEGER PRIMARY KEY, js TEXT, lua TEXT',
            'verse_meta' => 'verse_id INTEGER, meta_id INTEGER',
            'meta_resource' => 'meta_id INTEGER, resource_id INTEGER',
            'resource' => 'id INTEGER PRIMARY KEY',
            'property' => 'id INTEGER PRIMARY KEY, key TEXT',
            'verse_property' => 'verse_id INTEGER, property_id INTEGER',
            'group_verse' => 'group_id INTEGER, verse_id INTEGER',
            'group_user' => 'group_id INTEGER, user_id INTEGER',
            'manager' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, user_id INTEGER',
            'verse_space' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, space_id INTEGER',
            'space' => 'id INTEGER PRIMARY KEY, file_id INTEGER, image_id INTEGER, mesh_id INTEGER, data TEXT',
            'snapshot' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, uuid TEXT, code TEXT, data TEXT, metas TEXT, resources TEXT, space TEXT, managers TEXT, created_at TEXT, created_by INTEGER',
        ] as $name => $columns) {
            $this->db->createCommand("CREATE TABLE $name ($columns)")->execute();
        }
        $this->db->createCommand()->batchInsert('user', ['id'], [[7], [8]])->execute();
        $this->db->createCommand()->insert('verse', ['id' => 1, 'author_id' => 7, 'name' => 'Scene', 'uuid' => 'scene-1', 'data' => '{"children":{"modules":[]}}'])->execute();
        $this->db->createCommand()->insert('meta', ['id' => 2, 'author_id' => 7, 'title' => 'Entity', 'uuid' => 'entity-2', 'data' => '{"children":{"entities":[]}}'])->execute();
        require_once dirname(__DIR__, 3) . '/console/migrations/m260911_230000_add_webmcp_write_receipts.php';
        $migration = new \m260911_230000_add_webmcp_write_receipts(['db' => $this->db, 'compact' => true]);
        ob_start();
        $migration->safeUp();
        $migration->safeUp();
        ob_end_clean();
    }

    protected function tearDown(): void
    {
        foreach ($this->components as $name => $component) {
            Yii::$app->set($name, $component);
        }
        $this->db->close();
    }

    private function headers(string $id, string $revision): void
    {
        Yii::$app->request->headers->set('Idempotency-Key', $id);
        Yii::$app->request->headers->set('If-Match', '"' . $revision . '"');
    }

    public function testMigrationRepairsMissingIndexAndRejectsWeakerExistingIndex(): void
    {
        $migration = new \m260911_230000_add_webmcp_write_receipts(['db' => $this->db, 'compact' => true]);
        $this->db->createCommand()->dropIndex('uq_webmcp_actor_operation', 'webmcp_operation')->execute();
        ob_start();
        try {
            $migration->safeUp();
            $migration->assertSchema();
        } finally {
            ob_end_clean();
        }
        $this->db->createCommand()->dropIndex('uq_webmcp_actor_operation', 'webmcp_operation')->execute();
        $this->db->createCommand()->createIndex('uq_webmcp_actor_operation', 'webmcp_operation', ['actor_id', 'operation_id'])->execute();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected index definition');
        $migration->safeUp();
    }

    public function testExactMigrationPlanDoesNotWriteAndUpDoesNotRunUnrelatedMigrations(): void
    {
        $class = \console\controllers\WebMcpP1MigrateController::class;
        Yii::setAlias('@console', dirname(__DIR__, 3) . '/console');
        $controller = new $class('web-mcp-p1-migrate', Yii::$app, ['interactive' => false, 'compact' => true]);
        $this->assertTrue($controller->beforeAction(new \yii\base\InlineAction('plan', $controller, 'actionPlan')));
        $this->assertSame(0, $controller->actionPlan());
        $this->assertNull($this->db->getTableSchema('migration', true));
        $this->assertSame(0, $controller->actionUp(1));
        $this->assertSame([$class::EXACT_MIGRATION], (new Query())->select('version')->from('migration')->where(['not', ['version' => 'm000000_000000_base']])->column());
        $this->assertSame(0, $controller->actionPlan());
        $this->db->createCommand()->dropIndex('uq_webmcp_actor_operation', 'webmcp_operation')->execute();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing WebMCP unique constraint');
        $controller->actionPlan();
    }

    private function controller(string $type = 'verse'): VerseController|MetaController
    {
        return $type === 'verse' ? new VerseController('verse', Yii::$app) : new MetaController('meta', Yii::$app);
    }

    public function testSaveReplayAndConflictingIdReuse(): void
    {
        $operation = ReliableWrite::uuid();
        $original = Verse::findOne(1)->serverRevision;
        $this->headers($operation, $original);
        Yii::$app->request->setBodyParams(['name' => 'First change']);
        $first = $this->controller()->actionUpdate(1);
        $this->assertNotSame($original, $first['serverRevision']);
        $replay = $this->controller()->actionUpdate(1);
        $this->assertTrue($replay['replayed']);
        $this->assertEquals($first['writeReceipt'], $replay['writeReceipt']);
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        Yii::$app->request->setBodyParams(['name' => 'Different intent']);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('Key reuse must be rejected');
        } catch (ConflictHttpException) {
            $this->assertSame('First change', Verse::findOne(1)->name);
        }
    }

    public function testStaleTabCannotOverwriteAfterAnotherSave(): void
    {
        $original = Verse::findOne(1)->serverRevision;
        $this->headers(ReliableWrite::uuid(), $original);
        Yii::$app->request->setBodyParams(['name' => 'Newer']);
        $this->controller()->actionUpdate(1);
        $this->headers(ReliableWrite::uuid(), $original);
        Yii::$app->request->setBodyParams(['name' => 'Stale']);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('Stale save must fail');
        } catch (ConflictHttpException) {
            $this->assertSame('Newer', Verse::findOne(1)->name);
            $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        }
    }

    public function testReceiptInsertFailureRollsBackSnapshotAndOperation(): void
    {
        $this->db->createCommand()->update('verse', ['data' => '{"children":{"modules":[{"parameters":{"meta_id":2}}]}}'], ['id' => 1])->execute();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        Yii::$app->request->setBodyParams([]);
        $this->db->pdo->exec("CREATE TRIGGER reject_receipt BEFORE INSERT ON webmcp_operation BEGIN SELECT RAISE(ABORT, 'receipt write rejected'); END");
        try {
            $this->controller()->actionTakePhoto(1);
            $this->fail('A receipt insertion failure must roll back publication');
        } catch (\yii\db\Exception) {
            $this->assertSame(0, (int) (new Query())->from('snapshot')->count());
            $this->assertSame(0, (int) (new Query())->from('webmcp_operation')->count());
        }
    }

    public function testScriptSaveChangesRevisionAndIsIdempotent(): void
    {
        foreach (['verse' => 1, 'meta' => 2] as $type => $id) {
            $class = ReliableWrite::modelClass($type);
            $old = $class::findOne($id)->serverRevision;
            $this->headers(ReliableWrite::uuid(), $old);
            Yii::$app->request->setBodyParams(['blockly' => '{}', 'js' => 'console.log(1)', 'lua' => 'print(1)']);
            $result = $this->controller($type)->actionUpdateCode($id);
            $this->assertNotSame($old, $result['serverRevision']);
            $this->assertSame($result['serverRevision'], $class::findOne($id)->serverRevision);
            $this->assertTrue($this->controller($type)->actionUpdateCode($id)['replayed']);
        }
    }

    public function testFailedWriteRollsBackDataAndReceipt(): void
    {
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        try {
            ReliableWrite::run('verse', 1, 'save', [], fn () => null, function () {
                $this->db->createCommand()->update('verse', ['name' => 'Must roll back'], ['id' => 1])->execute();
                throw new \RuntimeException('injected failure');
            });
            $this->fail('Expected injected failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('injected failure', $error->getMessage());
            $this->assertSame('Scene', Verse::findOne(1)->name);
            $this->assertSame(0, (int) (new Query())->from('webmcp_operation')->count());
        }
    }

    public function testReceiptIsDurableAndBoundToActorTargetAndCurrentPermission(): void
    {
        $id = ReliableWrite::uuid();
        $this->headers($id, Meta::findOne(2)->serverRevision);
        Yii::$app->request->setBodyParams(['title' => 'Changed entity']);
        $saved = $this->controller('meta')->actionUpdate(2);
        $this->assertEquals($saved['writeReceipt'], $this->controller('meta')->actionOperation(2, $id));
        $this->assertStringNotContainsString('Changed entity', (string) (new Query())->from('webmcp_operation')->select('receipt')->scalar());
        try {
            $this->controller()->actionOperation(1, $id);
            $this->fail('Wrong target must not find the receipt');
        } catch (NotFoundHttpException) {}
        Yii::$app->user->switchIdentity(new ReceiptIdentity(8));
        $this->expectException(ForbiddenHttpException::class);
        $this->controller('meta')->actionOperation(2, $id);
    }

    public function testReadRevisionDoesNotPersistLegacyCodeHydration(): void
    {
        $this->db->createCommand()->insert('code', ['id' => 10, 'js' => 'old()', 'lua' => 'old()'])->execute();
        $this->db->createCommand()->insert('meta_code', ['meta_id' => 2, 'code_id' => 10, 'blockly' => '{}'])->execute();
        $revision = Meta::findOne(2)->serverRevision;
        $this->assertStringStartsWith('sha256:', $revision);
        $this->assertNull((new Query())->from('meta_code')->select('js')->scalar());
    }

    public function testMissingGuardHalfIsRejectedAndLegacySaveStillWorks(): void
    {
        Yii::$app->request->headers->set('Idempotency-Key', ReliableWrite::uuid());
        Yii::$app->request->setBodyParams(['name' => 'Legacy']);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('Incomplete guards must fail');
        } catch (\yii\web\BadRequestHttpException) {}
        Yii::$app->request->headers->remove('Idempotency-Key');
        $this->controller()->actionUpdate(1);
        $this->assertSame('Legacy', Verse::findOne(1)->name);
    }

    public function testPublicationReceiptSurvivesLaterMutableSnapshotChangesWithoutReplaying(): void
    {
        $data = json_encode(['children' => ['modules' => [['parameters' => ['meta_id' => 2]]]]]);
        $this->db->createCommand()->update('verse', ['data' => $data], ['id' => 1])->execute();
        Yii::$app->request->setBodyParams([]);
        $operation = ReliableWrite::uuid();
        $this->headers($operation, Verse::findOne(1)->serverRevision);
        $first = $this->controller()->actionTakePhoto(1);
        $this->assertArrayNotHasKey('publicationRevision', $first);
        $this->assertArrayNotHasKey('contentHash', $first);
        $this->assertNull($this->db->getTableSchema('scene_publication_revision', true));
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        $this->db->createCommand()->update('snapshot', ['code' => 'later publication'], ['id' => $first['id']])->execute();
        $retry = $this->controller()->actionTakePhoto(1);
        $this->assertTrue($retry['replayed']);
        $this->assertEquals($first['writeReceipt'], $retry['writeReceipt']);
        $this->assertEquals($first['writeReceipt'], $this->controller()->actionOperation(1, $operation));
        $this->assertSame('later publication', (new Query())->from('snapshot')->select('code')->scalar());
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }

    public function testCurrentPublicationDoesNotRequireAnArchiveAndChecksPermission(): void
    {
        $this->assertFalse($this->controller()->actionPublication(1)['published']);
        $this->db->createCommand()->insert('snapshot', ['id' => 40, 'verse_id' => 1, 'uuid' => 'existing-snapshot'])->execute();
        $this->assertSame([
            'sceneId' => 1, 'published' => true, 'snapshotId' => 40,
            'snapshotUuid' => 'existing-snapshot', 'verification' => 'current_snapshot',
        ], $this->controller()->actionPublication(1));
        Yii::$app->user->switchIdentity(new ReceiptIdentity(8));
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionPublication(1);
    }

    public function testMissingReceiptStorageCannotLeaveAnUnacknowledgedCommit(): void
    {
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $this->db->createCommand()->dropTable('webmcp_operation')->execute();
        Yii::$app->request->setBodyParams(['name' => 'Must not commit']);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('Missing migration must fail closed');
        } catch (\yii\db\Exception) {
            $this->assertSame('Scene', Verse::findOne(1)->name);
        }
    }

    public function testNewRoutesResolveUnderStrictRouting(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $manager = new \yii\web\UrlManager($config['components']['urlManager']);
        $oldMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        try {
            $uuid = ReliableWrite::uuid();
            foreach ([
                ["v1/verses/1/publication", 'v1/verse/publication'],
                ["v1/verses/1/operations/$uuid", 'v1/verse/operation'],
                ["v1/metas/2/operations/$uuid", 'v1/meta/operation'],
            ] as [$path, $route]) {
                Yii::$app->request->setPathInfo($path);
                $parsed = $manager->parseRequest(Yii::$app->request);
                $this->assertIsArray($parsed);
                $this->assertSame($route, $parsed[0]);
            }
            Yii::$app->request->setPathInfo("v1/verses/1/publication/$uuid");
            $this->assertFalse($manager->parseRequest(Yii::$app->request));
        } finally {
            if ($oldMethod === null) unset($_SERVER['REQUEST_METHOD']);
            else $_SERVER['REQUEST_METHOD'] = $oldMethod;
        }
    }

    public function testUnknownSceneCannotReturnPublicationState(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionPublication(999);
    }
}

final class ReceiptIdentity implements IdentityInterface
{
    public function __construct(public int $id) {}
    public static function findIdentity($id): ?self { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null): ?self { return null; }
    public function getId(): int { return $this->id; }
    public function getAuthKey(): string { return ''; }
    public function validateAuthKey($authKey): bool { return false; }
}
