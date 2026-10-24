<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Verse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\ActionEvent;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\rest\Serializer;
use yii\web\BadRequestHttpException;
use yii\web\ErrorHandler;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\UrlManager;
use yii\web\User;

final class SceneAllListingTest extends TestCase
{
    private static int $actorSequence = 100000;
    private array $components = [];
    private array $actors;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['db', 'request', 'response', 'user', 'authManager', 'errorHandler'] as $id) {
            $this->components[$id] = Yii::$app->get($id, false);
        }
        $base = self::$actorSequence += 10;
        $this->actors = ['root' => $base, 'admin' => $base + 1, 'alice' => $base + 2,
            'bob' => $base + 3, 'literal' => $base + 4];
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->set('request', new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']));
        Yii::$app->set('response', new Response());
        Yii::$app->set('authManager', new DbManager());
        Yii::$app->set('errorHandler', new ErrorHandler());
        Yii::$app->set('user', new User([
            'identityClass' => SceneAllIdentity::class, 'enableSession' => false, 'loginUrl' => null,
        ]));
        $db = Yii::$app->db;
        $db->open();
        foreach ([
            'user' => 'id INTEGER PRIMARY KEY, username TEXT, nickname TEXT, email TEXT, password_hash TEXT',
            'verse' => 'id INTEGER PRIMARY KEY, author_id INTEGER, updater_id INTEGER, name TEXT, description TEXT, image_id INTEGER, uuid TEXT, data TEXT, info TEXT, created_at TEXT, updated_at TEXT',
            'verse_code' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, blockly TEXT, lua TEXT, js TEXT',
            'group_verse' => 'group_id INTEGER, verse_id INTEGER',
            'group_user' => 'group_id INTEGER, user_id INTEGER',
            'property' => 'id INTEGER PRIMARY KEY, key TEXT, info TEXT',
            'verse_property' => 'verse_id INTEGER, property_id INTEGER',
            'tags' => 'id INTEGER PRIMARY KEY, name TEXT, key TEXT',
            'verse_tags' => 'verse_id INTEGER, tags_id INTEGER',
            'auth_item' => 'name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER',
            'auth_item_child' => 'parent TEXT, child TEXT',
            'auth_assignment' => 'item_name TEXT, user_id TEXT, created_at INTEGER',
            'auth_rule' => 'name TEXT PRIMARY KEY, data TEXT, created_at INTEGER, updated_at INTEGER',
        ] as $table => $columns) {
            $db->createCommand("CREATE TABLE $table ($columns)")->execute();
        }
        foreach ($this->actors as $name => $id) {
            $db->createCommand()->insert('user', ['id' => $id,
                'username' => $name === 'literal' ? 'percent%_username' : $name,
                'nickname' => $name === 'alice' ? '果园老师' : ucfirst($name) . ' Teacher',
                'email' => 'private@example.com', 'password_hash' => 'private hash'])->execute();
            $role = Yii::$app->authManager->getRole($name === 'root' || $name === 'admin' ? $name : 'user');
            if ($role === null) {
                $role = Yii::$app->authManager->createRole($name === 'root' || $name === 'admin' ? $name : 'user');
                Yii::$app->authManager->add($role);
            }
            Yii::$app->authManager->assign($role, $id);
        }
        foreach ([1001 => ['alice', 'Alpha public'], 1002 => ['bob', 'Beta private'],
            1003 => ['root', 'Root private'], 1004 => ['alice', 'Alpha private'],
            1005 => ['literal', 'Special private']] as $id => [$author, $name]) {
            $db->createCommand()->insert('verse', ['id' => $id, 'author_id' => $this->actors[$author],
                'updater_id' => $this->actors[$author], 'name' => $name, 'description' => 'Scene description',
                'uuid' => 'scene-' . $id, 'data' => '{}', 'info' => '{}'])->execute();
        }
        $db->createCommand()->batchInsert('property', ['id', 'key'], [[1, 'public'], [2, 'checkin'], [3, 'unused']])->execute();
        $db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'],
            [[1001, 1], [1002, 2], [1003, 2], [1004, 2], [1004, 2]])->execute();
        $db->createCommand()->batchInsert('tags', ['id', 'name', 'key'], [[1, 'Category', 'category'], [2, 'Public', 'public']])->execute();
        $db->createCommand()->batchInsert('verse_tags', ['verse_id', 'tags_id'], [[1001, 1], [1002, 1], [1005, 2]])->execute();
        $db->createCommand()->insert('group_verse', ['group_id' => 88, 'verse_id' => 1002])->execute();
        $db->createCommand()->insert('group_user', ['group_id' => 88, 'user_id' => $this->actors['alice']])->execute();
        $this->actAs('root');
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        foreach ($this->components as $id => $component) Yii::$app->set($id, $component);
        parent::tearDown();
    }

    public function testRootSeesEveryAuthorIncludingPrivateScenesInStableDefaultOrder(): void
    {
        $provider = $this->controller()->actionAll();
        $this->assertSame(5, $provider->getTotalCount());
        $this->assertSame([1005, 1004, 1003, 1002, 1001], array_column($provider->getModels(), 'id'));
        $this->assertSame([1003], array_column($this->controller()->actionIndex()->getModels(), 'id'));
    }

    public function testMyScenesAlwaysMeansAuthoredByMeAndDoesNotIncludeCollaborativeScenes(): void
    {
        $this->actAs('alice');
        $this->assertTrue(Verse::findOne(1002)->editable);
        Yii::$app->request->setQueryParams(['scope' => 'all', 'propertyId' => '2', 'authorSearch' => 'bob']);
        $this->assertEqualsCanonicalizing([1001, 1004], array_column($this->controller()->actionIndex()->getModels(), 'id'));
        Yii::$app->request->setQueryParams(['VerseSearch' => ['author_id' => $this->actors['bob']]]);
        $this->assertSame([], $this->controller()->actionIndex()->getModels());
    }

    #[DataProvider('authorQueries')]
    public function testAuthorSearchMatchesUsernameOrNicknameLiterally(string $search, array $expected): void
    {
        $this->assertSame($expected, $this->ids(['authorSearch' => $search, 'sort' => 'id']));
    }

    public static function authorQueries(): array
    {
        return [['alice', [1001, 1004]], ['  果园  ', [1001, 1004]], ['Bob Teacher', [1002]],
            ['%_', [1005]], ["' OR 1=1 --", []], ['', [1001, 1002, 1003, 1004, 1005]]];
    }

    public function testAuthorNamePropertyAndClassificationFiltersComposeWithoutDuplicates(): void
    {
        $this->assertSame([1004], $this->ids(['authorId' => (string) $this->actors['alice'],
            'authorSearch' => '果园', 'VerseSearch' => ['name' => 'Alpha'], 'propertyId' => '2']));
        $this->assertSame([1004, 1003, 1002], $this->ids(['propertyId' => '2']));
        $this->assertSame([1002], $this->ids(['tags' => '1', 'public' => '0']));
        $this->assertSame([1001], $this->ids(['tags' => '1', 'public' => '1']));
        $this->assertSame([], $this->ids(['tags' => '2']));
        $this->assertSame([], $this->ids(['propertyId' => '3']));
        $this->assertSame([], $this->ids(['authorId' => '999999999']));
    }

    public function testPaginationAndExpandedAuthorsUseExistingSerializationWithoutPrivateUserFields(): void
    {
        Yii::$app->request->setQueryParams(['sort' => 'id', 'page' => '2', 'per-page' => '2', 'expand' => 'author,public']);
        $provider = $this->controller()->actionAll();
        $provider->pagination->route = 'v1/verse/all';
        $serialized = (new Serializer())->serialize($provider);
        $this->assertSame([1003, 1004], array_column($serialized, 'id'));
        $this->assertSame('root', $serialized[0]['author']['username']);
        $this->assertSame('果园老师', $serialized[1]['author']['nickname']);
        $this->assertArrayNotHasKey('email', $serialized[0]['author']);
        $this->assertArrayNotHasKey('password_hash', $serialized[0]['author']);
        $this->assertTrue($serialized[0]['editable']);
        $this->assertFalse($serialized[1]['editable']);
        $this->assertFalse($serialized[1]['public']);
        $this->assertSame('5', (string) Yii::$app->response->headers->get('X-Pagination-Total-Count'));
        $this->assertSame('2', (string) Yii::$app->response->headers->get('X-Pagination-Current-Page'));
        $this->assertSame('2', (string) Yii::$app->response->headers->get('X-Pagination-Per-Page'));
    }

    public function testRootPrivateDetailIsIndependentOfListFiltersAndDoesNotGrantOtherObjectPermissions(): void
    {
        Yii::$app->request->setQueryParams(['propertyId' => '3', 'authorSearch' => 'alice']);
        $verse = $this->controller()->actionAllScene('1002');
        $this->assertSame(1002, $verse->id);
        $this->assertFalse($verse->editable);
        $this->assertFalse($verse->viewable);
        foreach (['view', 'update', 'delete'] as $action) {
            try {
                $this->controller()->checkAccess($action, $verse);
                $this->fail('Root read entry must not change ordinary object authorization');
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
            }
        }
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionAllScene('999999');
    }

    #[DataProvider('nonRootActors')]
    public function testAuthenticatedNonRootCannotAccessEitherAction(string $actor, string $action): void
    {
        $this->actAs($actor);
        $this->expectException(ForbiddenHttpException::class);
        $action === 'all' ? $this->controller()->actionAll() : $this->controller()->actionAllScene(1001);
    }

    public static function nonRootActors(): array
    {
        return [['admin', 'all'], ['admin', 'all-scene'], ['alice', 'all'], ['alice', 'all-scene']];
    }

    #[DataProvider('actionIds')]
    public function testAuthenticationRunsBeforeExplicitRootCheck(string $actionId): void
    {
        $this->actAs(null);
        $controller = $this->controller();
        $auth = Yii::createObject($controller->behaviors()['authenticator'] + [
            'user' => Yii::$app->user, 'request' => Yii::$app->request, 'response' => Yii::$app->response,
        ]);
        $this->expectException(UnauthorizedHttpException::class);
        $auth->beforeAction($controller->createAction($actionId));
    }

    #[DataProvider('actionIds')]
    public function testRootPassesRealRouteFilterWithoutRouteGrants(string $actionId): void
    {
        $this->assertSame([], Yii::$app->authManager->getPermissionsByUser($this->actors['root']));
        $controller = $this->controller();
        $access = Yii::createObject($controller->behaviors()['access']);
        $access->attach($controller);
        $event = new ActionEvent($controller->createAction($actionId));
        $access->beforeFilter($event);
        $this->assertTrue($event->isValid);
        $access->detach();
    }

    public static function actionIds(): array { return [['all'], ['all-scene']]; }

    #[DataProvider('invalidQueries')]
    public function testInvalidFiltersReturn400InsteadOfBroadeningOrFailingQueries(array $params): void
    {
        Yii::$app->request->setQueryParams($params);
        $this->expectException(BadRequestHttpException::class);
        $this->controller()->actionAll();
    }

    public static function invalidQueries(): array
    {
        return [[['authorSearch' => []]], [['authorSearch' => true]], [['authorSearch' => str_repeat('长', 256)]],
            [['authorSearch' => "\xFF"]], [['authorId' => '-1']], [['authorId' => '999999999999999999999']],
            [['propertyId' => '0']], [['propertyId' => []]], [['page' => '0']], [['page' => '1000001']],
            [['per-page' => '101']], [['per-page' => '1.5']], [['sort' => []]], [['expand' => []]],
            [['tags' => []]], [['tags' => '1,2 OR 1=1']], [['tags' => implode(',', array_fill(0, 101, '1'))]],
            [['public' => 'maybe']], [['VerseSearch' => 'name']], [['VerseSearch' => ['name' => []]]],
            [['VerseSearch' => ['name' => str_repeat('x', 256)]]], [['VerseSearch' => ['author_id' => 'invalid']]]];
    }

    public function testUnknownPropertyReturns404(): void
    {
        Yii::$app->request->setQueryParams(['propertyId' => '999999']);
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionAll();
    }

    public function testMalformedDetailIdReturns400(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->controller()->actionAllScene('0 OR 1=1');
    }

    public function testRoutesResolveReadsAndPreflightFromConfigurationSource(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = array_values(array_filter($config['components']['urlManager']['rules'],
            static fn ($rule) => is_array($rule) && ($rule['controller'] ?? null) === 'v1/verse'));
        $manager = new UrlManager(['enablePrettyUrl' => true, 'showScriptName' => false, 'enableStrictParsing' => true, 'rules' => $rules]);
        foreach (['all' => [], 'all/1002' => ['id' => '1002']] as $path => $params) {
            foreach (['GET', 'HEAD', 'OPTIONS', 'POST', 'DELETE', 'PUT'] as $method) {
                $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
                $request->setPathInfo('v1/verses/' . $path);
                $request->headers->set('X-Http-Method-Override', $method);
                $actual = $manager->parseRequest($request);
                if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
                    $action = $method === 'OPTIONS' ? 'options' : ($params === [] ? 'all' : 'all-scene');
                    $this->assertSame(['v1/verse/' . $action, $params], $actual);
                } else {
                    $this->assertFalse($actual);
                }
            }
        }
    }

    private function ids(array $params): array
    {
        Yii::$app->request->setQueryParams($params);
        return array_column($this->controller()->actionAll()->getModels(), 'id');
    }

    private function actAs(?string $actor): void
    {
        Yii::$app->user->setIdentity($actor === null ? null : new SceneAllIdentity($this->actors[$actor]));
    }

    private function controller(): VerseController { return new VerseController('verse', Yii::$app); }
}

final class SceneAllIdentity implements IdentityInterface
{
    public function __construct(private int $id) {}
    public static function findIdentity($id) { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return ''; }
    public function validateAuthKey($authKey) { return false; }
}
