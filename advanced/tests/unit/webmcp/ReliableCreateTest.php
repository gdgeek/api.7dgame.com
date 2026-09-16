<?php

namespace tests\unit\webmcp;

use api\modules\v1\controllers\MetaController;
use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Verse;
use api\modules\v1\services\ReliableWrite;
use api\modules\v1\services\ReliableCreate;
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
final class ReliableCreateTest extends TestCase
{
    private array $components;
    private Connection $db;
    protected function setUp(): void
    {
        $this->components = [];
        foreach (['db', 'user', 'request'] as $name) {
            $this->components[$name] = Yii::$app->has($name) ? Yii::$app->get($name) : null;
        }
        $this->db = new Connection(['dsn' => 'sqlite::memory:', 'pdoClass' => class_exists('Pdo\Sqlite') ? 'Pdo\Sqlite' : 'PDO']);
        $this->db->open();
        $createFunction = method_exists($this->db->pdo, 'createFunction') ? 'createFunction' : 'sqliteCreateFunction';
        $this->db->pdo->{$createFunction}('NOW', fn() => '2026-09-11 23:00:00');
        Yii::$app->set('db', $this->db);
        Yii::$app->set('request', new Request(['cookieValidationKey' => 'test', 'scriptUrl' => '', 'hostInfo' => 'http://localhost']));
        Yii::$app->set('user', new User(['identityClass' => CreationIdentity::class, 'enableSession' => false, 'loginUrl' => null]));
        Yii::$app->user->switchIdentity(new CreationIdentity(7));
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
            $this->db->createCommand("CREATE TABLE {$name} ({$columns})")->execute();
        }
        $this->db->createCommand()->batchInsert('user', ['id'], [[7], [8]])->execute();
        $this->db->createCommand()->insert('verse', ['id' => 1, 'author_id' => 7, 'name' => 'Scene', 'uuid' => 'scene-1', 'data' => '{"children":{"modules":[]}}'])->execute();
        $this->db->createCommand()->insert('meta', [
            'id' => 2,
            'author_id' => 7,
            'title' => 'Entity',
            'uuid' => 'entity-2',
            'data' => '{"children":{"entities":[]}}',
        ])->execute();
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
    }
    private function create(string $key, string $kind = 'meta', ?array $body = null): array
    {
        Yii::$app->request->headers->set('Idempotency-Key', $key);
        $body ??= $kind === 'meta' ? ['title' => 'Entity', 'uuid' => ReliableWrite::uuid()] : ['name' => 'Scene', 'uuid' => ReliableWrite::uuid()];
        return ReliableCreate::run($kind, $body, function ($model) {
            if ((int) $model->author_id !== (int) Yii::$app->user->id) {
                throw new ForbiddenHttpException('Revoked');
            }
        });
    }
    public function testSameKeySamePayloadReplaysOriginalObjectAfterResponseLoss(): void
    {
        $key = ReliableWrite::uuid();
        $body = ['title' => '幂等🧪', 'uuid' => ReliableWrite::uuid()];
        $one = $this->create($key, 'meta', $body);
        $two = $this->create($key, 'meta', $body);
        $this->assertSame($one['id'], $two['id']);
        $this->assertTrue($two['replayed']);
        $this->assertEquals($one['writeReceipt'], $two['writeReceipt']);
        $queried = ReliableCreate::receipt('meta', $key, fn() => null);
        $this->assertSame($one['id'], $queried['id']);
        $this->assertSame(1, (int) (new Query())->from('meta')->where(['uuid' => $body['uuid']])->count());
    }
    public function testDifferentBodyOrTargetCannotReuseKey(): void
    {
        $key = ReliableWrite::uuid();
        $body = ['title' => 'Entity', 'uuid' => ReliableWrite::uuid()];
        $this->create($key, 'meta', $body);
        foreach ([
            ['meta', ['title' => 'Different', 'uuid' => $body['uuid']]],
            ['verse', ['name' => 'Scene', 'uuid' => $body['uuid']]],
        ] as [$type, $args]) {
            try {
                $this->create($key, $type, $args);
                $this->fail('Key reused');
            } catch (ConflictHttpException) {
            }
        }
        $this->assertSame(1, (int) (new Query())->from('webmcp_operation')->count());
    }
    public function testFailedCreationRollsBackKeyAndObjectTogether(): void
    {
        $key = ReliableWrite::uuid();
        try {
            $this->create($key, 'meta', ['title' => str_repeat('x', 1000), 'uuid' => ReliableWrite::uuid()]);
            $this->fail('Invalid object accepted');
        } catch (\yii\web\BadRequestHttpException) {
        }
        $this->assertSame(0, (int) (new Query())->from('webmcp_operation')->where(['operation_id' => $key])->count());
        $this->assertSame(1, (int) (new Query())->from('meta')->count());
        $this->assertGreaterThan(0, $this->create($key)['id']);
    }
    public function testActorIsolationAndPermissionCheckedOnReplay(): void
    {
        $key = ReliableWrite::uuid();
        $body = ['name' => 'Scene', 'uuid' => ReliableWrite::uuid()];
        $one = $this->create($key, 'verse', $body);
        Yii::$app->user->switchIdentity(new CreationIdentity(8));
        try {
            ReliableCreate::receipt('verse', $key, fn() => null);
            $this->fail('Other actor receipt leaked');
        } catch (NotFoundHttpException) {
        }
        $two = $this->create($key, 'verse', ['name' => 'Other', 'uuid' => ReliableWrite::uuid()]);
        $this->assertNotSame($one['id'], $two['id']);
        Yii::$app->user->switchIdentity(new CreationIdentity(7));
        $this->db->createCommand()->update('verse', ['author_id' => 8], ['id' => $one['id']])->execute();
        $this->expectException(ForbiddenHttpException::class);
        $this->create($key, 'verse', $body);
    }
    public function testNewEndpointsResolveFromSourceConfig(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $manager = new \yii\web\UrlManager($config['components']['urlManager']);
        $old = $_SERVER['REQUEST_METHOD'] ?? null;
        $id = ReliableWrite::uuid();
        try {
            foreach ([
                ['GET', "v1/metas/create-operations/{$id}", 'v1/meta/create-operation'],
                ['GET', "v1/verses/create-operations/{$id}", 'v1/verse/create-operation'],
                ['GET', 'v1/authoring-tasks', 'v1/authoring-task/index'],
                ['POST', 'v1/authoring-tasks', 'v1/authoring-task/create'],
                ['GET', "v1/authoring-tasks/{$id}", 'v1/authoring-task/view'],
                ['OPTIONS', "v1/authoring-tasks/{$id}/claim", 'v1/authoring-task/options'],
                ['OPTIONS', "v1/authoring-tasks/{$id}/checkpoint", 'v1/authoring-task/options'],
                ['OPTIONS', 'v1/authoring-tasks', 'v1/authoring-task/options'],
                ['OPTIONS', "v1/metas/create-operations/{$id}", 'v1/meta/options'],
                ['POST', "v1/authoring-tasks/{$id}/claim", 'v1/authoring-task/claim'],
                ['PUT', "v1/authoring-tasks/{$id}/checkpoint", 'v1/authoring-task/checkpoint'],
            ] as [$method, $path, $expected]) {
                $_SERVER['REQUEST_METHOD'] = $method;
                Yii::$app->request->setPathInfo($path);
                $parsed = $manager->parseRequest(Yii::$app->request);
                $this->assertIsArray($parsed, $path);
                $this->assertSame($expected, $parsed[0]);
            }
        } finally {
            if ($old === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $old;
            }
        }
    }
    public function testLegacyVerseCreationActionIsKeptUnlessKeyIsPresent(): void
    {
        $controller = new VerseController('verse', Yii::$app);
        $this->assertArrayHasKey('create', $controller->actions());
        Yii::$app->request->headers->set('Idempotency-Key', ReliableWrite::uuid());
        $this->assertArrayNotHasKey('create', $controller->actions());
    }
    public function testDeletedObjectIsNotRecreated(): void
    {
        $key = ReliableWrite::uuid();
        $body = ['title' => 'Entity', 'uuid' => ReliableWrite::uuid()];
        $one = $this->create($key, 'meta', $body);
        $this->db->createCommand()->delete('meta', ['id' => $one['id']])->execute();
        $this->expectException(NotFoundHttpException::class);
        $this->create($key, 'meta', $body);
    }
}
final class CreationIdentity implements IdentityInterface
{
    public function __construct(public int $id)
    {
    }
    public static function findIdentity($id): ?self
    {
        return new self((int) $id);
    }
    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getAuthKey(): string
    {
        return '';
    }
    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
