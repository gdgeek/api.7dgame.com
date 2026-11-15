<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseController;
use api\modules\v1\controllers\SnapshotController;
use api\modules\v1\controllers\SystemController;
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

final class OrganizationSceneListingTest extends TestCase
{
    private static int $sequence = 300000;
    private array $components = [];
    private array $actors = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['db', 'request', 'response', 'user', 'authManager', 'errorHandler'] as $id) {
            $this->components[$id] = Yii::$app->get($id, false);
        }
        $base = self::$sequence += 10;
        foreach (['manager', 'unbound', 'member', 'north', 'south', 'outside', 'admin', 'root', 'admin-unbound'] as $offset => $name) {
            $this->actors[$name] = $base + $offset;
        }
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:', 'pdoClass' => \Pdo\Sqlite::class]));
        Yii::$app->set('request', new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']));
        Yii::$app->set('response', new Response());
        Yii::$app->set('authManager', new DbManager());
        Yii::$app->set('errorHandler', new ErrorHandler());
        Yii::$app->set('user', new User(['identityClass' => OrganizationSceneIdentity::class, 'enableSession' => false, 'loginUrl' => null]));
        foreach ([
            'user' => 'id INTEGER PRIMARY KEY, username TEXT, nickname TEXT, email TEXT, password_hash TEXT',
            'organization' => 'id INTEGER PRIMARY KEY, title TEXT, name TEXT',
            'user_organization' => 'user_id INTEGER, organization_id INTEGER',
            'verse' => 'id INTEGER PRIMARY KEY, author_id INTEGER, updater_id INTEGER, name TEXT, description TEXT, image_id INTEGER, uuid TEXT, data TEXT, info TEXT, created_at TEXT, updated_at TEXT',
            'verse_code' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, code_id INTEGER, blockly TEXT, lua TEXT, js TEXT',
            'group_verse' => 'group_id INTEGER, verse_id INTEGER',
            'group_user' => 'group_id INTEGER, user_id INTEGER',
            'property' => 'id INTEGER PRIMARY KEY, key TEXT, info TEXT',
            'verse_property' => 'verse_id INTEGER, property_id INTEGER',
            'tags' => 'id INTEGER PRIMARY KEY, name TEXT, key TEXT',
            'verse_tags' => 'verse_id INTEGER, tags_id INTEGER',
            'verse_space' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, space_id INTEGER',
            'space' => 'id INTEGER PRIMARY KEY, name TEXT, user_id INTEGER, mesh_id INTEGER, file_id INTEGER, image_id INTEGER, data TEXT',
            'verse_meta' => 'verse_id INTEGER, meta_id INTEGER',
            'meta' => 'id INTEGER PRIMARY KEY, name TEXT',
            'resource' => 'id INTEGER PRIMARY KEY',
            'meta_resource' => 'meta_id INTEGER, resource_id INTEGER',
            'manager' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, user_id INTEGER, type TEXT, data TEXT',
            'file' => 'id INTEGER PRIMARY KEY, url TEXT',
            'snapshot' => 'id INTEGER PRIMARY KEY, verse_id INTEGER, uuid TEXT, code TEXT, data TEXT, metas TEXT, resources TEXT, space TEXT, managers TEXT, created_at TEXT, created_by INTEGER',
            'auth_item' => 'name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER',
            'auth_item_child' => 'parent TEXT, child TEXT',
            'auth_assignment' => 'item_name TEXT, user_id TEXT, created_at INTEGER',
            'auth_rule' => 'name TEXT PRIMARY KEY, data TEXT, created_at INTEGER, updated_at INTEGER',
        ] as $table => $columns) {
            Yii::$app->db->createCommand("CREATE TABLE $table ($columns)")->execute();
        }
        $auth = Yii::$app->authManager;
        foreach (['manager', 'user', 'admin', 'root'] as $name) {
            $auth->add($auth->createRole($name));
        }
        $auth->addChild($auth->getRole('root'), $auth->getRole('admin'));
        $auth->addChild($auth->getRole('admin'), $auth->getRole('manager'));
        foreach ($this->actors as $name => $id) {
            Yii::$app->db->createCommand()->insert('user', ['id' => $id, 'username' => $name, 'nickname' => ucfirst($name), 'email' => 'private@example.com'])->execute();
            $role = in_array($name, ['manager', 'unbound'], true) ? 'manager'
                : ($name === 'admin-unbound' ? 'admin' : (in_array($name, ['admin', 'root'], true) ? $name : 'user'));
            $auth->assign($auth->getRole($role), $id);
        }
        Yii::$app->db->createCommand()->batchInsert('organization', ['id', 'title', 'name'], [[1, 'North', 'north'], [2, 'South', 'south'], [3, 'Outside', 'outside']])->execute();
        foreach (['manager' => [1, 2], 'member' => [1], 'north' => [1, 2], 'south' => [2], 'outside' => [3], 'admin' => [1], 'root' => [1]] as $name => $organizations) {
            foreach ($organizations as $organization) {
                Yii::$app->db->createCommand()->insert('user_organization', ['user_id' => $this->actors[$name], 'organization_id' => $organization])->execute();
            }
        }
        foreach ([1 => ['north', 'North private'], 2 => ['south', 'South private'], 3 => ['outside', 'Outside private'], 4 => ['manager', 'Own private'], 5 => ['outside', 'Outside public']] as $id => [$author, $name]) {
            Yii::$app->db->createCommand()->insert('verse', ['id' => $id, 'author_id' => $this->actors[$author], 'updater_id' => $this->actors[$author], 'name' => $name, 'uuid' => 'scene-' . $id, 'data' => '{}', 'info' => '{}'])->execute();
        }
        Yii::$app->db->createCommand()->insert('property', ['id' => 1, 'key' => 'public'])->execute();
        Yii::$app->db->createCommand()->insert('verse_property', ['verse_id' => 5, 'property_id' => 1])->execute();
        Yii::$app->db->createCommand()->insert('tags', ['id' => 1, 'name' => 'Class', 'key' => 'class'])->execute();
        Yii::$app->db->createCommand()->insert('verse_tags', ['verse_id' => 1, 'tags_id' => 1])->execute();
        Yii::$app->db->createCommand()->insert('verse_code', ['id' => 1, 'verse_id' => 1, 'blockly' => '<xml/>', 'js' => 'print(1)', 'lua' => 'print(2)'])->execute();
        Yii::$app->db->createCommand()->insert('space', ['id' => 1, 'name' => 'Organization space', 'user_id' => $this->actors['north'], 'data' => '{}'])->execute();
        Yii::$app->db->createCommand()->insert('verse_space', ['id' => 1, 'verse_id' => 1, 'space_id' => 1])->execute();
        $this->actAs('manager');
        Yii::$app->db->pdo->createFunction('NOW', static fn () => '2026-11-15 21:00:00');
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        foreach ($this->components as $id => $component) Yii::$app->set($id, $component);
        parent::tearDown();
    }

    public function testManagerReadsAllSharedOrganizationsIncludingPrivateWithoutDuplicateScenes(): void
    {
        $provider = $this->controller()->actionOrganization();
        $this->assertSame(3, $provider->getTotalCount());
        $this->assertSame([4, 2, 1], array_column($provider->getModels(), 'id'));
        $this->assertSame([4], array_column($this->controller()->actionIndex()->getModels(), 'id'));
        $this->assertSame([5], array_column($this->controller()->actionPublic()->getModels(), 'id'));
        $this->assertTrue(Verse::findOne(4)->editable);
    }

    public function testAdminListsOnlyItsOrganizationsIncludingPrivateScenes(): void
    {
        $this->actAs('admin');
        $provider = $this->controller()->actionOrganization();
        $this->assertSame(2, $provider->getTotalCount());
        $this->assertSame([4, 1], array_column($provider->getModels(), 'id'));
        Yii::$app->request->setQueryParams(['organizationId' => '2', 'scope' => 'all']);
        $this->assertSame([4, 1], array_column($this->controller()->actionOrganization()->getModels(), 'id'));
        $this->assertSame([], $this->controller()->actionIndex()->getModels());
        $this->assertFalse(Verse::findOne(1)->editable);
        $this->assertFalse(Verse::findOne(2)->viewable);
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionOrganizationScene(2);
    }

    public function testFiltersAndPaginationNeverBroadenOrganizationScope(): void
    {
        foreach ([['organizationId' => '3', 'scope' => 'all'], ['VerseSearch' => ['author_id' => $this->actors['outside']]], ['authorId' => $this->actors['outside']]] as $params) {
            Yii::$app->request->setQueryParams($params);
            $this->assertSame($params === ['organizationId' => '3', 'scope' => 'all'] ? [4, 2, 1] : [], array_column($this->controller()->actionOrganization()->getModels(), 'id'));
        }
        Yii::$app->request->setQueryParams(['VerseSearch' => ['name' => 'private'], 'tags' => '1', 'public' => '0']);
        $this->assertSame([1], array_column($this->controller()->actionOrganization()->getModels(), 'id'));
        Yii::$app->request->setQueryParams(['sort' => 'id', 'page' => '2', 'per-page' => '1', 'expand' => 'author']);
        $provider = $this->controller()->actionOrganization();
        $provider->pagination->route = 'v1/verse/organization';
        $serialized = (new Serializer())->serialize($provider);
        $this->assertSame([2], array_column($serialized, 'id'));
        $this->assertSame('south', $serialized[0]['author']['username']);
        $this->assertArrayNotHasKey('email', $serialized[0]['author']);
        $this->assertFalse($serialized[0]['editable']);
        $this->assertTrue($serialized[0]['viewable']);
        $this->assertSame('3', (string) Yii::$app->response->headers->get('X-Pagination-Total-Count'));
    }

    #[DataProvider('organizationReaders')]
    public function testStandardDetailAndPreviewExpandAreReadableButWritesRemainDenied(string $actor): void
    {
        $this->actAs($actor);
        Yii::$app->request->setQueryParams(['expand' => 'verseCode,js,lua,space,space.mesh,space.file,space.image,metas,resources']);
        $verse = $this->controller()->createAction('view')->run(1);
        $serialized = (new Serializer())->serialize($verse);
        $this->assertTrue($serialized['viewable']);
        $this->assertFalse($serialized['editable']);
        $this->assertSame('<xml/>', $serialized['verseCode']['blockly']);
        $this->assertSame('print(1)', $serialized['js']);
        $this->assertSame('print(2)', $serialized['lua']);
        $this->assertSame('Organization space', $serialized['space']['name']);
        $this->assertSame([], $serialized['metas']);
        $this->assertSame([], $serialized['resources']);
        foreach (['update', 'delete'] as $action) {
            try {
                $this->controller()->checkAccess($action, $verse);
                $this->fail('Organization reading must not grant ' . $action);
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
            }
        }
    }

    #[DataProvider('organizationReaders')]
    public function testForeignPrivateSceneRemainsUnreadableEvenWhenClientClaimsOrganizationScope(string $actor): void
    {
        $this->actAs($actor);
        Yii::$app->request->setQueryParams(['organizationId' => '3', 'scope' => 'organization']);
        $this->assertFalse(Verse::findOne(3)->viewable);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->createAction('view')->run(3);
    }

    #[DataProvider('organizationReaders')]
    public function testOrganizationDetailRejectsForeignAndPublicOutsideScenes(string $actor): void
    {
        $this->actAs($actor);
        $this->assertSame(1, $this->controller()->actionOrganizationScene(1)->id);
        foreach ([3, 5, 99999] as $id) {
            try {
                $this->controller()->actionOrganizationScene($id);
                $this->fail('Outside scene must be hidden from organization detail');
            } catch (NotFoundHttpException $error) {
                $this->assertSame(404, $error->statusCode);
            }
        }
    }

    #[DataProvider('organizationReaders')]
    public function testMembershipRemovalTakesEffectOnAlreadyLoadedSceneWithoutStalePermission(string $actor): void
    {
        $this->actAs($actor);
        $verse = Verse::findOne(1);
        $this->assertTrue($verse->viewable);
        Yii::$app->db->createCommand()->delete('user_organization', ['user_id' => $this->actors[$actor]])->execute();
        $this->assertFalse($verse->viewable);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionOrganization();
    }

    #[DataProvider('organizationReaders')]
    public function testRoleRemovalRevokesOrganizationReadingWithoutStalePermission(string $actor): void
    {
        $this->actAs($actor);
        $verse = Verse::findOne(1);
        $this->assertTrue($verse->viewable);
        Yii::$app->authManager->revoke(Yii::$app->authManager->getRole($actor), $this->actors[$actor]);
        $this->assertFalse($verse->viewable);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionOrganizationScene(1);
    }

    public function testAdminOriginalOwnerAndCollaborationEditingRemainsUnchanged(): void
    {
        $this->actAs('admin');
        Yii::$app->db->createCommand()->update('verse', ['author_id' => $this->actors['admin']], ['id' => 4])->execute();
        Yii::$app->db->createCommand()->insert('group_verse', ['group_id' => 1, 'verse_id' => 2])->execute();
        Yii::$app->db->createCommand()->insert('group_user', ['group_id' => 1, 'user_id' => $this->actors['admin']])->execute();
        $this->assertFalse(Verse::findOne(1)->editable);
        foreach ([4, 2] as $id) {
            $verse = Verse::findOne($id);
            $this->assertTrue($verse->editable);
            $this->controller()->checkAccess('update', $verse);
        }
    }

    #[DataProvider('organizationSnapshotReaders')]
    public function testLegacySnapshotWritesRejectOrganizationReadersWithoutChangingExistingSnapshot(string $actor, string $controllerClass): void
    {
        $this->actAs($actor);
        Yii::$app->db->createCommand()->insert('snapshot', ['id' => 1, 'verse_id' => 1, 'data' => 'original published data'])->execute();
        $this->assertTrue(Verse::findOne(1)->viewable);
        try {
            (new $controllerClass('snapshot', Yii::$app))->actionTakePhoto(1);
            $this->fail('Organization reading must not grant snapshot publication');
        } catch (ForbiddenHttpException $error) {
            $this->assertSame(403, $error->statusCode);
        }
        $this->assertSame('original published data', Yii::$app->db->createCommand('SELECT data FROM snapshot WHERE id=1')->queryScalar());
        $this->assertSame(1, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM snapshot')->queryScalar());
        $this->expectException(NotFoundHttpException::class);
        (new $controllerClass('snapshot', Yii::$app))->actionTakePhoto(99999);
    }

    #[DataProvider('legacySnapshotControllers')]
    public function testLegacySnapshotPublicationPreservesOwnerAndCollaboratorAccess(string $controllerClass): void
    {
        Yii::$app->db->createCommand()->insert('group_verse', ['group_id' => 1, 'verse_id' => 2])->execute();
        Yii::$app->db->createCommand()->insert('group_user', ['group_id' => 1, 'user_id' => $this->actors['manager']])->execute();
        foreach ([4, 2] as $sceneId) {
            Yii::$app->db->createCommand()->update('verse', ['data' => json_encode(['children' => ['modules' => [['parameters' => []]]]])], ['id' => $sceneId])->execute();
            $snapshot = (new $controllerClass('snapshot', Yii::$app))->actionTakePhoto($sceneId);
            $this->assertSame('scene-' . $sceneId, $snapshot['uuid']);
            $this->assertSame(1, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM snapshot WHERE verse_id=:id', [':id' => $sceneId])->queryScalar());
        }
    }

    public static function legacySnapshotControllers(): array { return [[SystemController::class], [SnapshotController::class]]; }

    public static function organizationReaders(): array { return [['manager'], ['admin']]; }

    public static function organizationSnapshotReaders(): array
    {
        return [['manager', SystemController::class], ['manager', SnapshotController::class],
            ['admin', SystemController::class], ['admin', SnapshotController::class]];
    }

    #[DataProvider('legacySnapshotControllers')]
    public function testLegacySnapshotRejectsArrayConditionsBeforeLookingUpScene(string $controllerClass): void
    {
        $this->expectException(BadRequestHttpException::class);
        (new $controllerClass('snapshot', Yii::$app))->actionTakePhoto(['author_id' => $this->actors['manager']]);
    }

    #[DataProvider('deniedActors')]
    public function testOnlyExplicitManagersOrAdminsWithMembershipCanUseNewEndpoints(?string $actor): void
    {
        $this->actAs($actor);
        foreach (['organization', 'organization-scene'] as $action) {
            try {
                $action === 'organization' ? $this->controller()->actionOrganization() : $this->controller()->actionOrganizationScene(1);
                $this->fail('Actor must not use organization scenes');
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
            }
        }
        if ($actor !== null) $this->assertFalse(Verse::findOne(1)->viewable);
    }

    public static function deniedActors(): array { return [['unbound'], ['member'], ['outside'], ['admin-unbound'], ['root'], [null]]; }

    public function testInvalidParametersFailClosed(): void
    {
        Yii::$app->request->setQueryParams(['VerseSearch' => ['author_id' => 'invalid']]);
        $this->expectException(BadRequestHttpException::class);
        $this->controller()->actionOrganization();
    }

    #[DataProvider('actions')]
    public function testRealAccessFilterAcceptsAuthenticatedActionAndJwtStillRejectsGuests(string $action): void
    {
        $controller = $this->controller();
        $access = Yii::createObject($controller->behaviors()['access']);
        $access->attach($controller);
        $event = new ActionEvent($controller->createAction($action));
        $access->beforeFilter($event);
        $this->assertTrue($event->isValid);
        $access->detach();
        $this->actAs(null);
        $auth = Yii::createObject($controller->behaviors()['authenticator'] + ['user' => Yii::$app->user, 'request' => Yii::$app->request, 'response' => Yii::$app->response]);
        $this->expectException(UnauthorizedHttpException::class);
        $auth->beforeAction($controller->createAction($action));
    }

    public static function actions(): array { return [['organization'], ['organization-scene']]; }

    public function testSourceRoutesAllowOnlyReadsAndPreflight(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = array_values(array_filter($config['components']['urlManager']['rules'], static fn ($rule) => is_array($rule) && ($rule['controller'] ?? null) === 'v1/verse'));
        $manager = new UrlManager(['enablePrettyUrl' => true, 'showScriptName' => false, 'enableStrictParsing' => true, 'rules' => $rules]);
        foreach (['organization' => [], 'organization/1' => ['id' => '1']] as $path => $params) {
            foreach (['GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'DELETE'] as $method) {
                $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
                $request->setPathInfo('v1/verses/' . $path);
                $request->headers->set('X-Http-Method-Override', $method);
                $result = $manager->parseRequest($request);
                if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
                    $action = $method === 'OPTIONS' ? 'options' : ($params === [] ? 'organization' : 'organization-scene');
                    $this->assertSame(['v1/verse/' . $action, $params], $result);
                } else {
                    $this->assertFalse($result);
                }
            }
        }
    }

    private function actAs(?string $name): void { Yii::$app->user->setIdentity($name === null ? null : new OrganizationSceneIdentity($this->actors[$name])); }
    private function controller(): VerseController { return new VerseController('verse', Yii::$app); }
}

final class OrganizationSceneIdentity implements IdentityInterface
{
    public function __construct(private int $id) {}
    public static function findIdentity($id) { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return ''; }
    public function validateAuthKey($authKey) { return false; }
}
