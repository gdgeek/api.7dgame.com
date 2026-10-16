<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\Verse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\ActionEvent;
use yii\base\InlineAction;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\web\BadRequestHttpException;
use yii\web\ErrorHandler;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\UrlManager;
use yii\web\User;

final class ScenePropertiesTest extends TestCase
{
    private array $originalComponents = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['db', 'request', 'response', 'user', 'authManager', 'errorHandler'] as $id) {
            $this->originalComponents[$id] = Yii::$app->get($id, false);
        }
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->set('request', new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']));
        Yii::$app->set('response', new Response());
        Yii::$app->set('errorHandler', new ErrorHandler());
        Yii::$app->set('authManager', new DbManager());
        Yii::$app->set('user', new User([
            'identityClass' => ScenePropertiesIdentity::class,
            'enableSession' => false,
            'loginUrl' => null,
        ]));
        $db = Yii::$app->db;
        $db->open();
        foreach ([
            'verse' => ['id' => 'pk', 'author_id' => 'integer'],
            'property' => ['id' => 'pk', 'key' => 'string not null unique', 'info' => 'text'],
            'verse_property' => ['id' => 'pk', 'verse_id' => 'integer', 'property_id' => 'integer'],
            'auth_item' => ['name' => 'string primary key', 'type' => 'integer', 'description' => 'text',
                'rule_name' => 'string', 'data' => 'text', 'created_at' => 'integer', 'updated_at' => 'integer'],
            'auth_item_child' => ['parent' => 'string', 'child' => 'string'],
            'auth_assignment' => ['item_name' => 'string', 'user_id' => 'string', 'created_at' => 'integer'],
            'auth_rule' => ['name' => 'string primary key', 'data' => 'text', 'created_at' => 'integer', 'updated_at' => 'integer'],
        ] as $table => $columns) {
            $db->createCommand()->createTable($table, $columns)->execute();
        }
        $db->createCommand()->batchInsert('verse', ['id', 'author_id'], [[1, 42], [2, 7]])->execute();
        $db->createCommand()->batchInsert('property', ['id', 'key', 'info'], [
            [1, 'public', 'Public visibility'], [2, 'checkin', 'Original info'], [3, 'other-scene', null],
        ])->execute();
        $db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'], [[1, 2], [2, 3]])->execute();

        $auth = Yii::$app->authManager;
        $root = $auth->createRole('root');
        $admin = $auth->createRole('admin');
        $member = $auth->createRole('user');
        foreach ([$root, $admin, $member] as $role) {
            $auth->add($role);
        }
        $auth->addChild($root, $admin);
        $auth->addChild($admin, $member);
        $auth->assign($root, 1);
        $auth->assign($admin, 2);
        $auth->assign($member, 42);
        $this->actAs(1);
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        foreach ($this->originalComponents as $id => $component) {
            Yii::$app->set($id, $component);
        }
        parent::tearDown();
    }

    public function testRootReadsOnlyAssociatedPropertiesOfAnotherAuthorsScene(): void
    {
        $this->assertSame([['id' => 2, 'key' => 'checkin', 'info' => 'Original info']],
            $this->controller()->actionProperties(1));
        $this->assertSame([['id' => 3, 'key' => 'other-scene', 'info' => null]],
            $this->controller()->actionProperties(2));
        Yii::$app->db->createCommand()->delete('verse_property', ['verse_id' => 2])->execute();
        $this->assertSame([], $this->controller()->actionProperties(2));
    }

    public function testRootPassesRealRouteFilterWithoutRouteGrantsAndCanReadAndAdd(): void
    {
        $this->assertTrue(Yii::$app->user->can('root'));
        $this->assertSame([], Yii::$app->authManager->getPermissionsByUser(1));
        $controller = $this->controller();
        $this->passRouteFilter($controller, 'properties');
        $this->assertSame([['id' => 2, 'key' => 'checkin', 'info' => 'Original info']],
            $controller->actionProperties(1));

        $this->passRouteFilter($controller, 'add-property');
        Yii::$app->request->setBodyParams(['key' => 'root-route-test']);
        $created = $controller->actionAddProperty(1);
        $this->assertTrue($created['success']);
        $this->assertSame('root-route-test', $created['property']['key']);
        $this->assertSame(['checkin', 'root-route-test'], array_column($controller->actionProperties(1), 'key'));
    }

    #[DataProvider('legacyActions')]
    public function testRouteBypassDoesNotApplyToExistingActions(string $action): void
    {
        $this->assertTrue(Yii::$app->user->can('root'));
        $this->expectException(ForbiddenHttpException::class);
        $this->passRouteFilter($this->controller(), $action);
    }

    public static function legacyActions(): array
    {
        return [['index'], ['update'], ['add-public'], ['remove-public']];
    }

    public function testRootCreatesPropertyThenReusesItWithoutOverwritingGlobalInfo(): void
    {
        Yii::$app->request->setBodyParams(['key' => '  featured  ', 'info' => 'Featured scene']);
        $created = $this->controller()->actionAddProperty(1);
        $this->assertTrue($created['success']);
        $this->assertSame('featured', $created['property']['key']);
        $this->assertSame('Featured scene', $created['property']['info']);

        Yii::$app->request->setBodyParams(['key' => 'featured', 'info' => 'Do not replace global metadata']);
        $this->assertSame($created, $this->controller()->actionAddProperty(1));
        $this->assertSame($created, $this->controller()->actionAddProperty(2));
        $this->assertSame(1, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property WHERE [[key]] = :key',
            [':key' => 'featured'])->queryScalar());
        $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property WHERE property_id = :id',
            [':id' => $created['property']['id']])->queryScalar());
    }

    public function testOptionalInfoAndMaximumKeyLength(): void
    {
        Yii::$app->request->setBodyParams(['key' => str_repeat('场', 255)]);
        $property = $this->controller()->actionAddProperty(1)['property'];
        $this->assertSame(str_repeat('场', 255), $property['key']);
        $this->assertNull($property['info']);

        Yii::$app->request->setBodyParams(['key' => 'max-info', 'info' => str_repeat('x', 65535)]);
        $this->assertSame(65535, strlen($this->controller()->actionAddProperty(1)['property']['info']));
    }

    public function testAddingPublicImmediatelyUsesExistingVisibilityAndLeavesOtherProperties(): void
    {
        $this->assertFalse(Verse::findOne(1)->public);
        Yii::$app->request->setBodyParams(['key' => 'public', 'info' => null]);
        $this->assertSame(['success' => true, 'property' => ['id' => 1, 'key' => 'public', 'info' => 'Public visibility']],
            $this->controller()->actionAddProperty(1));
        $this->assertTrue(Verse::findOne(1)->public);
        $this->assertSame(['public', 'checkin'], array_column($this->controller()->actionProperties(1), 'key'));

        $this->actAs(42);
        $this->controller()->actionRemovePublic(1);
        $this->assertFalse(Verse::findOne(1)->public);
        $this->actAs(1);
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
    }

    #[DataProvider('forbiddenActors')]
    public function testNonRootCannotReadOrAddEvenWithSceneOwnershipOrAdminRole(?int $userId, string $action): void
    {
        $this->actAs($userId);
        Yii::$app->request->setBodyParams(['key' => 'forbidden']);
        $controller = $this->controller();
        $this->passRouteFilter($controller, $action === 'actionProperties' ? 'properties' : 'add-property');
        try {
            $controller->$action(1);
            $this->fail('Only root should access scene property management');
        } catch (ForbiddenHttpException $error) {
            $this->assertSame(403, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
            $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property')->queryScalar());
        }
    }

    public static function forbiddenActors(): array
    {
        $cases = [];
        foreach (['admin' => 2, 'author' => 42, 'ordinary' => 7, 'guest' => null] as $role => $id) {
            foreach (['actionProperties', 'actionAddProperty'] as $action) {
                $cases[$role . '-' . $action] = [$id, $action];
            }
        }
        return $cases;
    }

    #[DataProvider('invalidBodies')]
    public function testInvalidBodiesFailWithoutAnyWrites(array $body): void
    {
        Yii::$app->request->setBodyParams($body);
        try {
            $this->controller()->actionAddProperty(1);
            $this->fail('Invalid property should fail');
        } catch (BadRequestHttpException $error) {
            $this->assertSame(400, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
            $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property')->queryScalar());
        }
    }

    public static function invalidBodies(): array
    {
        return [
            'missing key' => [[]], 'null key' => [['key' => null]], 'empty key' => [['key' => '']],
            'whitespace key' => [['key' => " \t\n"]], 'integer key' => [['key' => 3]],
            'array key' => [['key' => ['public']]], 'long key' => [['key' => str_repeat('场', 256)]],
            'invalid UTF-8 key' => [['key' => "\xFF"]], 'array info' => [['key' => 'x', 'info' => []]],
            'boolean info' => [['key' => 'x', 'info' => false]], 'numeric info' => [['key' => 'x', 'info' => 3]],
            'long info' => [['key' => 'x', 'info' => str_repeat('x', 65536)]],
            'invalid UTF-8 info' => [['key' => 'x', 'info' => "\xFF"]],
        ];
    }

    #[DataProvider('propertyActions')]
    public function testMissingSceneReturns404WithoutCreatingDictionaryEntries(string $action): void
    {
        Yii::$app->request->setBodyParams(['key' => 'missing-scene']);
        try {
            $this->controller()->$action(999);
            $this->fail('Missing scene should fail');
        } catch (NotFoundHttpException $error) {
            $this->assertSame(404, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
        }
    }

    #[DataProvider('propertyActions')]
    public function testBothActionsRequireJwt(string $action): void
    {
        $this->actAs(null);
        $controller = $this->controller();
        $auth = Yii::createObject($controller->behaviors()['authenticator'] + [
            'user' => Yii::$app->user, 'request' => Yii::$app->request, 'response' => Yii::$app->response,
        ]);
        $this->expectException(UnauthorizedHttpException::class);
        $auth->beforeAction(new InlineAction($action === 'actionProperties' ? 'properties' : 'add-property', $controller, $action));
    }

    public static function propertyActions(): array
    {
        return [['actionProperties'], ['actionAddProperty']];
    }

    #[DataProvider('disallowedMethods')]
    public function testActionsRejectOtherHttpMethods(string $action, string $method): void
    {
        Yii::$app->request->headers->set('X-Http-Method-Override', $method);
        Yii::$app->request->setBodyParams(['key' => 'wrong-method']);
        $this->expectException(MethodNotAllowedHttpException::class);
        $this->controller()->runAction($action, ['id' => 1]);
    }

    public static function disallowedMethods(): array
    {
        return [['add-property', 'GET'], ['properties', 'POST'], ['add-property', 'DELETE']];
    }

    public function testRoutesResolveBothMethodsAndPreflightFromSourceConfig(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = array_values(array_filter($config['components']['urlManager']['rules'], static function ($rule): bool {
            return is_array($rule) && ($rule['controller'] ?? null) === 'v1/verse';
        }));
        $manager = new UrlManager(['enablePrettyUrl' => true, 'showScriptName' => false, 'rules' => $rules]);
        foreach (['GET' => 'properties', 'POST' => 'add-property', 'OPTIONS' => 'options'] as $method => $action) {
            $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
            $request->setPathInfo('v1/verses/2/properties');
            $request->headers->set('X-Http-Method-Override', $method);
            $this->assertSame(['v1/verse/' . $action, ['id' => '2']], $manager->parseRequest($request));
        }
    }

    private function actAs(?int $id): void
    {
        Yii::$app->user->setIdentity($id === null ? null : new ScenePropertiesIdentity($id));
    }

    private function passRouteFilter(VerseController $controller, string $actionId): void
    {
        $action = $controller->createAction($actionId);
        $this->assertNotNull($action);
        $access = Yii::createObject($controller->behaviors()['access']);
        $access->attach($controller);
        $event = new ActionEvent($action);
        $access->beforeFilter($event);
        $this->assertTrue($event->isValid);
        $access->detach();
    }

    private function controller(): VerseController
    {
        return new VerseController('verse', Yii::$app);
    }
}

final class ScenePropertiesIdentity implements IdentityInterface
{
    public function __construct(private int $id) {}
    public static function findIdentity($id) { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return ''; }
    public function validateAuthKey($authKey) { return false; }
}
