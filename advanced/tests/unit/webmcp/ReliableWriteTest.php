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
    private string|false $retentionEnvironment;

    protected function setUp(): void
    {
        $this->retentionEnvironment = getenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS');
        putenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS');
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
        require_once dirname(__DIR__, 3) . '/console/migrations/m260913_120000_add_scene_publication_history.php';
        (new \m260913_120000_add_scene_publication_history(['db' => $this->db, 'compact' => true]))->safeUp();
        ob_end_clean();
    }

    protected function tearDown(): void
    {
        foreach ($this->components as $name => $component) {
            Yii::$app->set($name, $component);
        }
        $this->db->close();
        putenv($this->retentionEnvironment === false ? 'WEBMCP_PUBLICATION_RETAINED_VERSIONS' : 'WEBMCP_PUBLICATION_RETAINED_VERSIONS=' . $this->retentionEnvironment);
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
        $this->assertStringStartsWith('sha256:', $first['contentHash']);
        $this->assertNotNull($this->db->getTableSchema('scene_publication_revision', true));
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        $this->db->createCommand()->update('snapshot', ['code' => 'later publication'], ['id' => $first['id']])->execute();
        $retry = $this->controller()->actionTakePhoto(1);
        $this->assertTrue($retry['replayed']);
        $this->assertEquals($first['writeReceipt'], $retry['writeReceipt']);
        $this->assertEquals($first['writeReceipt'], $this->controller()->actionOperation(1, $operation));
        $this->assertSame('later publication', (new Query())->from('snapshot')->select('code')->scalar());
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }

    private function publishable(): void
    {
        $this->db->createCommand()->update('verse', ['data' => '{"children":{"modules":[{"parameters":{"meta_id":2}}]}}'], ['id' => 1])->execute();
        Yii::$app->request->setBodyParams([]);
    }

    public function testArchivedAIsUnchangedAfterPublishingBAndLegacyCallCreatesAnotherVersion(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $first = $this->controller()->actionTakePhoto(1);
        $version = $first['publicationVersionId'];
        $original = $this->controller()->actionPublicationVersion(1, $version);
        $this->db->createCommand()->update('meta', ['title' => 'New entity', 'data' => '{"changed":true}'], ['id' => 2])->execute();
        $this->db->createCommand()->update('verse', ['name' => 'New name', 'description' => 'New description'], ['id' => 1])->execute();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $second = $this->controller()->actionTakePhoto(1);
        $this->assertSame($first['snapshotId'], $second['snapshotId']);
        $this->assertNotSame($version, $second['publicationVersionId']);
        $this->assertNotSame($first['contentHash'], $second['contentHash']);
        $this->assertSame($original, $this->controller()->actionPublicationVersion(1, $version));
        $body = json_decode($original['canonicalBody'], true);
        $this->assertSame('Scene', $body['scene']['name']);
        $this->assertSame('Entity', $body['runtime']['metas'][0]['title']);
        $this->assertSame($first['contentHash'], $original['contentHash']);
        $this->assertStringNotContainsString('canonicalBody', json_encode($this->controller()->actionPublications(1)));
        Yii::$app->request->headers->remove('Idempotency-Key');
        Yii::$app->request->headers->remove('If-Match');
        $third = $this->controller()->actionTakePhoto(1);
        $this->assertNotSame($second['publicationVersionId'], $third['publicationVersionId']);
        $this->assertSame(3, $this->controller()->actionPublications(1)['total']);
        $this->assertSame(2, (int) (new Query())->from('webmcp_operation')->count());
    }

    public function testArchiveFailureRollsBackExistingSnapshotAndReceipt(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $first = $this->controller()->actionTakePhoto(1);
        $before = (new Query())->from('snapshot')->one();
        $this->db->createCommand()->insert('verse_code', ['id' => 1, 'verse_id' => 1, 'lua' => 'changed'])->execute();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $this->db->pdo->exec("CREATE TRIGGER fail_archive BEFORE INSERT ON scene_publication_revision BEGIN SELECT RAISE(ABORT, 'archive failure'); END");
        try {
            $this->controller()->actionTakePhoto(1);
            $this->fail('Archive insertion must fail');
        } catch (\yii\db\Exception) {
            $this->assertSame($before, (new Query())->from('snapshot')->one());
            $this->assertSame(1, (int) (new Query())->from('scene_publication_revision')->count());
            $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        }
    }

    public function testSnapshotFailureLeavesNoArchiveOrReceipt(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $this->db->pdo->exec("CREATE TRIGGER fail_snapshot BEFORE INSERT ON snapshot BEGIN SELECT RAISE(ABORT, 'snapshot failure'); END");
        try {
            $this->controller()->actionTakePhoto(1);
            $this->fail('Snapshot insertion must fail');
        } catch (\yii\db\Exception) {
            foreach (['snapshot', 'scene_publication_revision', 'webmcp_operation'] as $table) {
                $this->assertSame(0, (int) (new Query())->from($table)->count());
            }
        }
    }

    public function testReceiptFailureRollsBackInitialSnapshotAndArchive(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $this->db->pdo->exec("CREATE TRIGGER fail_receipt BEFORE INSERT ON webmcp_operation BEGIN SELECT RAISE(ABORT, 'receipt failure'); END");
        try {
            $this->controller()->actionTakePhoto(1);
            $this->fail('Receipt insertion must fail');
        } catch (\yii\db\Exception) {
            $this->assertSame(0, (int) (new Query())->from('snapshot')->count());
            $this->assertSame(0, (int) (new Query())->from('scene_publication_revision')->count());
        }
    }

    public function testHistoryNeverFallsBackAndRechecksPermissionAndIntegrity(): void
    {
        $this->assertSame('history_unavailable', $this->controller()->actionPublications(1)['historyStatus']);
        $this->publishable();
        $first = $this->controller()->actionTakePhoto(1);
        $version = $first['publicationVersionId'];
        try {
            $this->controller()->actionPublicationVersion(1, ReliableWrite::uuid());
            $this->fail('Unknown version must not return current snapshot');
        } catch (NotFoundHttpException) {}
        Yii::$app->user->switchIdentity(new ReceiptIdentity(8));
        try {
            $this->controller()->actionPublicationVersion(1, $version);
            $this->fail('Current edit permission is required');
        } catch (ForbiddenHttpException) {}
        Yii::$app->user->switchIdentity(new ReceiptIdentity(7));
        $this->db->createCommand()->update('scene_publication_revision', ['canonical_body' => '{}'], ['publication_version_id' => $version])->execute();
        $this->expectExceptionMessage('publication_corrupt');
        $this->controller()->actionPublicationVersion(1, $version);
    }

    public function testHistoryKeysetPaginationAndLanguageIntent(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $first = $this->controller()->actionTakePhoto(1);
        Yii::$app->request->setQueryParams(['cl' => 'js']);
        try { $this->controller()->actionTakePhoto(1); $this->fail('Changed language must conflict'); }
        catch (ConflictHttpException) {}
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $second = $this->controller()->actionTakePhoto(1);
        Yii::$app->request->setQueryParams(['limit' => 1]);
        $page = $this->controller()->actionPublications(1);
        $this->assertSame($second['publicationVersionId'], $page['items'][0]['publicationVersionId']);
        Yii::$app->request->setQueryParams(['limit' => 1, 'before' => $page['nextBefore']]);
        $next = $this->controller()->actionPublications(1);
        $this->assertSame($first['publicationVersionId'], $next['items'][0]['publicationVersionId']);
        $this->assertNull($next['nextBefore']);
    }

    private function publishVersions(int $count): array
    {
        $this->publishable();
        $versions = [];
        for ($i = 0; $i < $count; $i++) {
            $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
            $versions[] = $this->controller()->actionTakePhoto(1);
        }
        return $versions;
    }

    public function testRetentionExpiresOnlyOldestBodyAfterTwentyAndPreservesReceipts(): void
    {
        $versions = $this->publishVersions(20);
        $original = $this->controller()->actionPublicationVersion(1, $versions[1]['publicationVersionId']);
        $snapshot = (new Query())->from('snapshot')->one();
        $other = (new Query())->from('scene_publication_revision')->one();
        unset($other['id']);
        $other['scene_id'] = 99;
        $other['publication_version_id'] = ReliableWrite::uuid();
        $other['operation_id'] = null;
        $this->db->createCommand()->insert('scene_publication_revision', $other)->execute();
        $plan = \api\modules\v1\services\PublicationArchive::retentionPlan(1, true);
        $this->assertSame(1, $plan['expiredVersionCount']);
        $this->assertSame($versions[0]['publicationVersionId'], $plan['versions'][0]['publicationVersionId']);
        $this->assertSame(20, $this->controller()->actionPublications(1)['total']); // Preview did not write.
        $new = $this->publishVersions(1)[0];
        $history = $this->controller()->actionPublications(1);
        $this->assertSame(20, $history['total']);
        $this->assertSame(['maxVersions' => 20, 'expiredVersions' => 1, 'policy' => 'latest_versions', 'expiredVersionHttpStatus' => 410], $history['retention']);
        $this->assertNull($history['nextBefore']);
        $this->assertSame($new['publicationVersionId'], $history['items'][0]['publicationVersionId']);
        $this->assertSame($original, $this->controller()->actionPublicationVersion(1, $versions[1]['publicationVersionId']));
        $expired = (new Query())->from('scene_publication_revision')->where(['publication_version_id' => $versions[0]['publicationVersionId']])->one();
        $this->assertSame('', $expired['canonical_body']);
        $this->assertSame(0, (int) $expired['byte_length']);
        $this->assertSame($other['canonical_body'], (new Query())->from('scene_publication_revision')->where(['scene_id' => 99])->select('canonical_body')->scalar());
        $this->assertSame($snapshot, (new Query())->from('snapshot')->one());
        try {
            $this->controller()->actionPublicationVersion(1, $versions[0]['publicationVersionId']);
            $this->fail('Expired version must return 410');
        } catch (\yii\web\HttpException $e) {
            $this->assertSame(410, $e->statusCode);
            $this->assertSame('publication_version_expired', $e->getMessage());
        }
        $this->headers($versions[0]['writeReceipt']['operationId'], Verse::findOne(1)->serverRevision);
        $replay = $this->controller()->actionTakePhoto(1);
        $this->assertTrue($replay['replayed']);
        $this->assertEquals($versions[0]['writeReceipt'], $replay['writeReceipt']);
        $this->assertSame(21, (int) (new Query())->from('webmcp_operation')->count());
        $this->assertSame(20, $this->controller()->actionPublications(1)['total']);
        Yii::$app->user->switchIdentity(new ReceiptIdentity(8));
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionPublicationVersion(1, $versions[0]['publicationVersionId']);
    }

    public function testCleanupAndNewPublicationBothRollBackIfReceiptFails(): void
    {
        $this->publishVersions(20);
        $archives = (new Query())->from('scene_publication_revision')->orderBy('id')->all();
        $snapshot = (new Query())->from('snapshot')->one();
        $this->db->createCommand()->update('verse', ['name' => 'Must roll back snapshot'], ['id' => 1])->execute();
        $this->db->pdo->exec("CREATE TRIGGER fail_retention_receipt BEFORE INSERT ON webmcp_operation BEGIN SELECT RAISE(ABORT, 'receipt failed'); END");
        try { $this->publishVersions(1); $this->fail('Receipt failure must abort publication'); }
        catch (\yii\db\Exception) {
            $this->assertSame($archives, (new Query())->from('scene_publication_revision')->orderBy('id')->all());
            $this->assertSame($snapshot, (new Query())->from('snapshot')->one());
            $this->assertSame(20, (int) (new Query())->from('webmcp_operation')->count());
        }
        $this->db->pdo->exec('DROP TRIGGER fail_retention_receipt');
        $this->db->pdo->exec("CREATE TRIGGER fail_cleanup BEFORE UPDATE ON scene_publication_revision BEGIN SELECT RAISE(ABORT, 'cleanup failed'); END");
        try { $this->publishVersions(1); $this->fail('Cleanup failure must abort publication'); }
        catch (\yii\db\Exception) {
            $this->assertSame($archives, (new Query())->from('scene_publication_revision')->orderBy('id')->all());
            $this->assertSame($snapshot, (new Query())->from('snapshot')->one());
        }
    }

    public function testLegacyPublicationUsesConfiguredRetentionAndPrunesBeforeCapacityCheck(): void
    {
        $versions = $this->publishVersions(2);
        putenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS=1');
        $this->db->createCommand()->update('scene_publication_revision', ['byte_length' => 512 * 1024 * 1024])->execute();
        Yii::$app->request->headers->remove('Idempotency-Key');
        Yii::$app->request->headers->remove('If-Match');
        $new = $this->controller()->actionTakePhoto(1);
        $history = $this->controller()->actionPublications(1);
        $this->assertSame(1, $history['total']);
        $this->assertSame(2, $history['retention']['expiredVersions']);
        $this->assertSame($new['publicationVersionId'], $history['items'][0]['publicationVersionId']);
        $this->assertLessThan(8 * 1024 * 1024, $history['totalBytes']);
        $this->assertSame(2, (int) (new Query())->from('webmcp_operation')->count());
    }

    public function testSizeAndCapacityFailureCannotPartiallyPublish(): void
    {
        $this->publishable();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        $this->controller()->actionTakePhoto(1);
        $before = (new Query())->from('snapshot')->one();
        $this->db->createCommand()->update('scene_publication_revision', ['byte_length' => \api\modules\v1\services\PublicationArchive::SCENE_BUDGET_BYTES])->execute();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        try { $this->controller()->actionTakePhoto(1); $this->fail('Capacity limit must fail'); }
        catch (\yii\web\HttpException $e) { $this->assertSame(507, $e->statusCode); }
        $this->assertSame($before, (new Query())->from('snapshot')->one());
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
        $this->assertTrue($this->controller()->actionPublications(1)['capacityWarning']);
        $this->db->createCommand()->insert('verse_code', ['id' => 1, 'verse_id' => 1, 'lua' => str_repeat('a', 8 * 1024 * 1024)])->execute();
        $this->headers(ReliableWrite::uuid(), Verse::findOne(1)->serverRevision);
        try { $this->controller()->actionTakePhoto(1); $this->fail('Body size limit must fail'); }
        catch (\yii\web\HttpException $e) { $this->assertSame(413, $e->statusCode); }
        $this->assertSame($before, (new Query())->from('snapshot')->one());
    }

    public function testExactP2MigrationPlanIsReadOnlyAndOnlyAppliesP2(): void
    {
        $this->db->createCommand()->dropTable('scene_publication_revision')->execute();
        $class = \console\controllers\WebMcpP2MigrateController::class;
        $controller = new $class('web-mcp-p2-migrate', Yii::$app, ['interactive' => false]);
        ob_start();
        try {
            $this->assertSame(0, (new $class('web-mcp-p2-migrate', Yii::$app, ['interactive' => false]))->runAction('plan'));
            $this->assertNull($this->db->getTableSchema('migration', true));
            $this->assertSame(0, (new $class('web-mcp-p2-migrate', Yii::$app, ['interactive' => false]))->runAction('up', [1]));
            $this->assertSame([$class::EXACT_MIGRATION], (new Query())->from('migration')->select('version')->where(['<>','version','m000000_000000_base'])->column());
            $this->assertSame(0, (new $class('web-mcp-p2-migrate', Yii::$app, ['interactive' => false]))->runAction('plan'));
        } finally { ob_end_clean(); }
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
                ["v1/verses/1/publications", 'v1/verse/publications'],
                ["v1/verses/1/publications/$uuid", 'v1/verse/publication-version'],
                ["v1/verses/1/operations/$uuid", 'v1/verse/operation'],
                ["v1/metas/2/operations/$uuid", 'v1/meta/operation'],
            ] as [$path, $route]) {
                Yii::$app->request->setPathInfo($path);
                $parsed = $manager->parseRequest(Yii::$app->request);
                $this->assertIsArray($parsed);
                $this->assertSame($route, $parsed[0]);
            }
            foreach (['v1/snapshots/1/take-photo', 'v1/system/take-photo', 'site/test'] as $path) {
                foreach (['GET', 'POST'] as $method) {
                    $_SERVER['REQUEST_METHOD'] = $method;
                    Yii::$app->request->setPathInfo($path);
                    $this->assertFalse($manager->parseRequest(Yii::$app->request), $path);
                }
            }
            $_SERVER['REQUEST_METHOD'] = 'GET';
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
