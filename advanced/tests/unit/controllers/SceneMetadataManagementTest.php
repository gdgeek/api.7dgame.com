<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\ActionEvent;
use yii\db\Connection;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ErrorHandler;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\User;

final class SceneMetadataManagementTest extends TestCase
{
    private static int $actorSequence = 2000;
    private array $components = [];
    private array $actors;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['db', 'user', 'request', 'authManager', 'errorHandler'] as $id) {
            $this->components[$id] = Yii::$app->get($id, false);
        }
        $base = self::$actorSequence += 10;
        $this->actors = ['root' => $base, 'author' => $base + 1, 'collaborator' => $base + 2,
            'admin' => $base + 3, 'user' => $base + 4];
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->set('request', new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']));
        Yii::$app->set('authManager', new DbManager());
        Yii::$app->set('errorHandler', new ErrorHandler());
        Yii::$app->set('user', new User([
            'identityClass' => SceneMetadataIdentity::class, 'enableSession' => false, 'loginUrl' => null,
        ]));
        $db = Yii::$app->db;
        $db->open();
        foreach ([
            'user' => 'id INTEGER PRIMARY KEY',
            'file' => 'id INTEGER PRIMARY KEY',
            'verse' => 'id INTEGER PRIMARY KEY, author_id INTEGER, updater_id INTEGER, name TEXT, description TEXT, image_id INTEGER, uuid TEXT, data TEXT, info TEXT, created_at TEXT, updated_at TEXT',
            'verse_code' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, blockly TEXT, lua TEXT, js TEXT',
            'verse_meta' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, meta_id INTEGER',
            'meta' => 'id INTEGER PRIMARY KEY',
            'group_verse' => 'group_id INTEGER, verse_id INTEGER',
            'group_user' => 'group_id INTEGER, user_id INTEGER',
            'property' => 'id INTEGER PRIMARY KEY, key TEXT',
            'verse_property' => 'verse_id INTEGER, property_id INTEGER',
            'tags' => 'id INTEGER PRIMARY KEY, name TEXT, key TEXT',
            'verse_tags' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, tags_id INTEGER',
            'auth_item' => 'name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER',
            'auth_item_child' => 'parent TEXT, child TEXT',
            'auth_assignment' => 'item_name TEXT, user_id TEXT, created_at INTEGER',
            'auth_rule' => 'name TEXT PRIMARY KEY, data TEXT, created_at INTEGER, updated_at INTEGER',
        ] as $table => $columns) {
            $db->createCommand("CREATE TABLE $table ($columns)")->execute();
        }
        foreach ($this->actors as $id) $db->createCommand()->insert('user', ['id' => $id])->execute();
        $db->createCommand()->insert('file', ['id' => 5])->execute();
        $db->createCommand()->insert('meta', ['id' => 88])->execute();
        $db->createCommand()->insert('verse', [
            'id' => 1, 'author_id' => $this->actors['author'], 'updater_id' => $this->actors['author'],
            'name' => 'Original scene', 'description' => 'Original description', 'uuid' => 'original-uuid',
            'data' => '{"children":{"modules":[]}}', 'info' => '{"version":"original"}',
        ])->execute();
        // Deliberately retain an old association: changing metadata must not rewrite the entity graph.
        $db->createCommand()->insert('verse_meta', ['verse_id' => 1, 'meta_id' => 77])->execute();
        $db->createCommand()->insert('verse_code', ['id' => 1, 'verse_id' => 1, 'js' => 'original script'])->execute();
        $db->createCommand()->insert('group_verse', ['group_id' => 9, 'verse_id' => 1])->execute();
        $db->createCommand()->insert('group_user', ['group_id' => 9, 'user_id' => $this->actors['collaborator']])->execute();
        $db->createCommand()->batchInsert('tags', ['id', 'name', 'key'], [[1, 'Category', 'category'], [2, 'Public', 'public']])->execute();
        foreach ($this->actors as $roleName => $id) {
            $role = Yii::$app->authManager->createRole($roleName);
            Yii::$app->authManager->add($role);
            Yii::$app->authManager->assign($role, $id);
        }
        require_once dirname(__DIR__, 3) . '/console/migrations/m260911_230000_add_webmcp_write_receipts.php';
        ob_start();
        (new \m260911_230000_add_webmcp_write_receipts(['db' => $db, 'compact' => true]))->safeUp();
        ob_end_clean();
        $this->actAs('root');
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        foreach ($this->components as $id => $component) Yii::$app->set($id, $component);
        parent::tearDown();
    }

    public function testRootUpdatesAnotherAuthorsMetadataAndKeepsContentAndAttribution(): void
    {
        $before = $this->sceneRow();
        $this->assertFalse(Verse::findOne(1)->editable);
        Yii::$app->request->setBodyParams(['name' => 'Managed name', 'description' => 'Managed description', 'image_id' => 5, 'version' => 'new-client-version']);
        $result = $this->controller()->actionUpdate(1);
        $after = $this->sceneRow();
        $this->assertSame('Managed name', $result['name']);
        $this->assertSame('Managed description', $after['description']);
        $this->assertSame(5, (int) $after['image_id']);
        $this->assertSame($this->actors['root'], (int) $after['updater_id']);
        foreach (['author_id', 'uuid', 'data', 'info', 'created_at', 'updated_at'] as $field) {
            $this->assertSame($before[$field], $after[$field], $field);
        }
        $this->assertSame([77], array_map('intval', (new Query())->select('meta_id')->from('verse_meta')->column()));
        $this->assertSame('original script', (new Query())->select('js')->from('verse_code')->scalar());
        $this->assertFalse(Verse::findOne(1)->editable);
    }

    #[DataProvider('forbiddenCrossAuthorBodies')]
    public function testRootRejectsProtectedUnknownAndEmptyBodiesWithoutPartialWrites(array $body): void
    {
        $before = $this->sceneRow();
        Yii::$app->request->setBodyParams($body);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('Cross-author writes require a non-empty metadata-only payload');
        } catch (BadRequestHttpException $error) {
            $this->assertSame(400, $error->statusCode);
            $this->assertSame($before, $this->sceneRow());
        }
    }

    public static function forbiddenCrossAuthorBodies(): array
    {
        $cases = ['empty' => [[]], 'version only' => [['version' => 'ignored']]];
        foreach (['data', 'info', 'metas', 'author_id', 'updater_id', 'uuid', 'created_at', 'updated_at', 'code', 'js', 'public', 'tags', 'unknown'] as $field) {
            $cases[$field] = [['name' => 'Must not save', $field => 'protected']];
        }
        return $cases;
    }

    #[DataProvider('nonManagingRoles')]
    public function testNonRootUsersCannotModifyAnotherAuthorsMetadataOrTags(string $role): void
    {
        $this->actAs($role);
        $before = $this->sceneRow();
        Yii::$app->request->setBodyParams(['name' => 'Denied']);
        foreach ([fn () => $this->controller()->actionUpdate(1), fn () => $this->controller()->actionAddTag(1, 1),
            fn () => $this->controller()->actionRemoveTag(1, 1)] as $operation) {
            try {
                $operation();
                $this->fail('Only root may manage information without scene edit permission');
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
                $this->assertSame($before, $this->sceneRow());
            }
        }
        $this->assertSame([], $this->controller()->allowAction());
    }

    public static function nonManagingRoles(): array { return [['admin'], ['user']]; }

    #[DataProvider('editingRoles')]
    public function testAuthorAndCollaboratorRetainContentEditingAndClassificationTags(string $role): void
    {
        $this->actAs($role);
        Yii::$app->request->setBodyParams(['name' => 'Existing editor', 'info' => 'Editor info',
            'data' => '{"children":{"modules":[{"parameters":{"meta_id":88}}]}}']);
        $this->controller()->actionUpdate(1);
        $this->assertSame('Editor info', $this->sceneRow()['info']);
        $this->assertSame($this->actors['author'], (int) $this->sceneRow()['author_id']);
        $this->assertSame([88], array_map('intval', (new Query())->select('meta_id')->from('verse_meta')->column()));
        $this->assertTrue($this->controller()->actionAddTag(1, 1)['success']);
        $this->assertTrue($this->controller()->actionRemoveTag(1, 1)['success']);
    }

    public static function editingRoles(): array { return [['author'], ['collaborator']]; }

    public function testRootManagesClassificationTagsWithoutGrantingGeneralSceneEditing(): void
    {
        $this->assertTrue($this->controller()->actionAddTag(1, 1)['success']);
        $this->assertSame(1, (int) (new Query())->from('verse_tags')->count());
        $this->assertTrue($this->controller()->actionRemoveTag(1, 1)['success']);
        $this->assertSame(0, (int) (new Query())->from('verse_tags')->count());
        foreach (['update', 'delete'] as $action) {
            try {
                $this->controller()->checkAccess($action, Verse::findOne(1));
                $this->fail('Metadata management must not broaden general scene access');
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
            }
        }
        Yii::$app->request->setBodyParams(['js' => 'Denied script']);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionUpdateCode(1);
    }

    public function testRootStillCannotUseClassificationEndpointToPublishScene(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->controller()->actionAddTag(1, 2);
    }

    public function testRootMetadataWritesRetainReliableWriteRevisionAndReceiptChecks(): void
    {
        $revision = Verse::findOne(1)->serverRevision;
        $key = ReliableWrite::uuid();
        Yii::$app->request->headers->set('Idempotency-Key', $key);
        Yii::$app->request->headers->set('If-Match', '"' . $revision . '"');
        Yii::$app->request->setBodyParams(['description' => 'Reliable update', 'version' => 'ignored']);
        $first = $this->controller()->actionUpdate(1);
        $replay = $this->controller()->actionUpdate(1);
        $this->assertTrue($replay['replayed']);
        $this->assertEquals($first['writeReceipt'], $replay['writeReceipt']);
        $this->assertSame($this->actors['root'], (int) (new Query())->select('actor_id')->from('webmcp_operation')->scalar());
        Yii::$app->request->headers->set('Idempotency-Key', ReliableWrite::uuid());
        Yii::$app->request->setBodyParams(['description' => 'Stale update']);
        $this->expectException(ConflictHttpException::class);
        $this->controller()->actionUpdate(1);
    }

    public function testRootMetadataRoutesPassTheExistingFilterWithoutOtherRouteGrants(): void
    {
        $this->assertSame([], Yii::$app->authManager->getPermissionsByUser($this->actors['root']));
        foreach (['update', 'add-tag', 'remove-tag'] as $id) {
            $controller = $this->controller();
            $action = $controller->createAction($id);
            $access = Yii::createObject($controller->behaviors()['access']);
            $access->attach($controller);
            $event = new ActionEvent($action);
            $access->beforeFilter($event);
            $this->assertTrue($event->isValid);
            $access->detach();
        }
        $this->assertNotContains('update-code', $this->controller()->allowAction());
        $this->assertNotContains('delete', $this->controller()->allowAction());
    }

    public function testNonRootStillNeedsItsExistingRouteGrant(): void
    {
        $this->actAs('author');
        $controller = $this->controller();
        $access = Yii::createObject($controller->behaviors()['access']);
        $access->attach($controller);
        $this->expectException(ForbiddenHttpException::class);
        $access->beforeFilter(new ActionEvent($controller->createAction('update')));
    }

    public function testRootRetainsFullEditingForItsOwnScene(): void
    {
        Yii::$app->db->createCommand()->update('verse', ['author_id' => $this->actors['root']], ['id' => 1])->execute();
        Yii::$app->request->setBodyParams(['info' => 'Own editor data', 'data' => '{"children":{"modules":[]}}']);
        $this->controller()->actionUpdate(1);
        $this->assertSame('Own editor data', $this->sceneRow()['info']);
        $this->assertSame($this->actors['root'], (int) $this->sceneRow()['author_id']);
    }

    public function testInvalidMetadataDoesNotPartiallySaveOtherFields(): void
    {
        $before = $this->sceneRow();
        Yii::$app->request->setBodyParams(['name' => 'Do not save', 'image_id' => 999]);
        try {
            $this->controller()->actionUpdate(1);
            $this->fail('An unknown cover must fail model validation');
        } catch (BadRequestHttpException $error) {
            $this->assertSame($before, $this->sceneRow());
        }
    }

    private function actAs(string $role): void
    {
        Yii::$app->user->switchIdentity(new SceneMetadataIdentity($this->actors[$role]));
    }

    private function controller(): VerseController { return new VerseController('verse', Yii::$app); }
    private function sceneRow(): array { return (new Query())->from('verse')->where(['id' => 1])->one(); }
}

final class SceneMetadataIdentity implements IdentityInterface
{
    public function __construct(private int $id) {}
    public static function findIdentity($id) { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return ''; }
    public function validateAuthKey($authKey) { return false; }
}
