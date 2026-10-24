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
            'verse' => ['id' => 'pk', 'author_id' => 'integer', 'updater_id' => 'integer', 'image_id' => 'integer',
                'created_at' => 'datetime', 'updated_at' => 'datetime', 'name' => 'string', 'info' => 'text', 'data' => 'text'],
            'property' => ['id' => 'pk', 'key' => 'string not null unique', 'info' => 'text'],
            'verse_property' => ['id' => 'pk', 'verse_id' => 'integer', 'property_id' => 'integer'],
            'group_verse' => ['group_id' => 'integer', 'verse_id' => 'integer'],
            'group_user' => ['group_id' => 'integer', 'user_id' => 'integer'],
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
        $manager = $auth->createRole('manager');
        foreach ([$root, $admin, $member, $manager] as $role) {
            $auth->add($role);
        }
        $auth->addChild($root, $admin);
        $auth->addChild($admin, $member);
        $auth->assign($root, 1);
        $auth->assign($admin, 2);
        $auth->assign($manager, 3);
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

        $this->passRouteFilter($controller, 'property-options');
        $this->assertSame(['checkin', 'other-scene', 'public'], array_column($controller->actionPropertyOptions(1), 'key'));

        $this->passRouteFilter($controller, 'add-property');
        Yii::$app->request->setBodyParams(['key' => 'root-route-test']);
        $created = $controller->actionAddProperty(1);
        $this->assertTrue($created['success']);
        $this->assertSame('root-route-test', $created['property']['key']);
        $this->assertSame(['checkin', 'root-route-test'], array_column($controller->actionProperties(1), 'key'));

        $this->passRouteFilter($controller, 'remove-property');
        $this->assertSame($created, $controller->actionRemoveProperty(1, $created['property']['id']));
        $this->assertSame(['checkin'], array_column($controller->actionProperties(1), 'key'));

        $this->passRouteFilter($controller, 'property-usage');
        $this->assertSame(['property' => $created['property'], 'sceneCount' => 0],
            $controller->actionPropertyUsage(1, $created['property']['id']));
        $this->passRouteFilter($controller, 'delete-property-globally');
        $this->assertSame($created + ['affectedSceneCount' => 0],
            $controller->actionDeletePropertyGlobally(1, $created['property']['id']));
    }

    public function testRootListsTheEntireDictionaryIncludingAssociatedAndUnassociatedProperties(): void
    {
        $this->assertSame([
            ['id' => 2, 'key' => 'checkin', 'info' => 'Original info'],
            ['id' => 3, 'key' => 'other-scene', 'info' => null],
            ['id' => 1, 'key' => 'public', 'info' => 'Public visibility'],
        ], $this->controller()->actionPropertyOptions(1));
        $this->assertSame($this->controller()->actionPropertyOptions(1), $this->controller()->actionPropertyOptions(2));
    }

    public function testRootAssociatesExistingPropertyByIdWithoutChangingTheDictionary(): void
    {
        Yii::$app->request->setBodyParams(['propertyId' => 3]);
        $expected = ['success' => true, 'property' => ['id' => 3, 'key' => 'other-scene', 'info' => null]];
        $this->assertSame($expected, $this->controller()->actionAddProperty(1));
        $this->assertSame($expected, $this->controller()->actionAddProperty(1));
        $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
        $this->assertSame(1, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property WHERE verse_id = 1 AND property_id = 3')->queryScalar());
        $this->assertSame(['checkin', 'other-scene'], array_column($this->controller()->actionProperties(1), 'key'));
    }

    public function testUnknownPropertyIdReturns404WithoutAnyWrites(): void
    {
        Yii::$app->request->setBodyParams(['propertyId' => 999]);
        try {
            $this->controller()->actionAddProperty(1);
            $this->fail('Unknown property should fail');
        } catch (NotFoundHttpException $error) {
            $this->assertSame(404, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
            $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property')->queryScalar());
        }
    }

    public function testChoosingExistingPublicPropertyImmediatelyChangesVisibility(): void
    {
        $this->assertFalse(Verse::findOne(1)->public);
        Yii::$app->request->setBodyParams(['propertyId' => 1]);
        $this->controller()->actionAddProperty(1);
        $this->assertTrue(Verse::findOne(1)->public);
        $this->assertSame('Public visibility', $this->controller()->actionProperties(1)[0]['info']);
    }

    public function testRootRemovesAllMatchingSceneAssociationsAndRetainsDictionaryAndOtherScenes(): void
    {
        Yii::$app->db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'],
            [[1, 2], [2, 2], [1, 1]])->execute();
        $expected = ['success' => true, 'property' => ['id' => 2, 'key' => 'checkin', 'info' => 'Original info']];
        $this->assertSame($expected, $this->controller()->actionRemoveProperty(1, 2));
        $this->assertSame($expected, $this->controller()->actionRemoveProperty(1, 2));
        $this->assertSame(['public'], array_column($this->controller()->actionProperties(1), 'key'));
        $this->assertSame(['checkin', 'other-scene'], array_column($this->controller()->actionProperties(2), 'key'));
        $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
        $this->assertSame(0, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property WHERE verse_id = 1 AND property_id = 2')->queryScalar());
        $this->assertSame('Original info', Yii::$app->db->createCommand('SELECT info FROM property WHERE id = 2')->queryScalar());
    }

    public function testRemovingUnassociatedExistingPropertyIsSuccessfulAndDoesNotAffectOtherScenes(): void
    {
        $this->assertSame(['success' => true, 'property' => ['id' => 3, 'key' => 'other-scene', 'info' => null]],
            $this->controller()->actionRemoveProperty(1, 3));
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
        $this->assertSame(['other-scene'], array_column($this->controller()->actionProperties(2), 'key'));
    }

    public function testRemovingPublicImmediatelyMakesScenePrivateAndKeepsOtherProperties(): void
    {
        Yii::$app->request->setBodyParams(['propertyId' => 1]);
        $this->controller()->actionAddProperty(1);
        $this->controller()->actionAddProperty(2);
        $this->assertTrue(Verse::findOne(1)->public);
        $this->assertSame(['success' => true, 'property' => ['id' => 1, 'key' => 'public', 'info' => 'Public visibility']],
            $this->controller()->actionRemoveProperty(1, 1));
        $this->assertFalse(Verse::findOne(1)->public);
        $this->assertTrue(Verse::findOne(2)->public);
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
        $this->assertSame(['checkin', 'other-scene', 'public'], array_column($this->controller()->actionPropertyOptions(1), 'key'));
    }

    public function testRemovingUnknownPropertyReturns404WithoutAnyWrites(): void
    {
        try {
            $this->controller()->actionRemoveProperty(1, 999);
            $this->fail('Unknown property should fail');
        } catch (NotFoundHttpException $error) {
            $this->assertSame(404, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
            $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property')->queryScalar());
        }
    }

    public function testUsageCountsDistinctScenesAcrossAuthorsAndNotOnlyCurrentScene(): void
    {
        Yii::$app->db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'],
            [[1, 2], [2, 2]])->execute();
        $this->assertSame(['property' => ['id' => 2, 'key' => 'checkin', 'info' => 'Original info'], 'sceneCount' => 2],
            $this->controller()->actionPropertyUsage(2, 2));
        $this->assertSame(0, $this->controller()->actionPropertyUsage(1, 1)['sceneCount']);
    }

    public function testGlobalDeleteRemovesDictionaryAndEveryDuplicateAcrossScenesButRetainsScenesAndOtherProperties(): void
    {
        Yii::$app->db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'],
            [[1, 2], [2, 2]])->execute();
        $this->assertSame(['success' => true, 'property' => ['id' => 2, 'key' => 'checkin', 'info' => 'Original info'],
            'affectedSceneCount' => 2], $this->controller()->actionDeletePropertyGlobally(1, 2));
        $this->assertSame([], $this->controller()->actionProperties(1));
        $this->assertSame(['other-scene'], array_column($this->controller()->actionProperties(2), 'key'));
        $this->assertSame(['other-scene', 'public'], array_column($this->controller()->actionPropertyOptions(1), 'key'));
        $this->assertSame(2, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse')->queryScalar());
        $this->assertSame(0, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property WHERE property_id = 2')->queryScalar());
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionDeletePropertyGlobally(1, 2);
    }

    public function testGlobalDeleteCanStartFromUnassociatedSceneAndDeleteUnusedProperty(): void
    {
        $deleted = $this->controller()->actionDeletePropertyGlobally(1, 3);
        $this->assertSame(1, $deleted['affectedSceneCount']);
        $this->assertSame([], $this->controller()->actionProperties(2));
        $deleted = $this->controller()->actionDeletePropertyGlobally(1, 1);
        $this->assertSame(0, $deleted['affectedSceneCount']);
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
    }

    public function testGlobalDeletePublicMakesAllScenesPrivateAndAllowsExplicitLaterRecreation(): void
    {
        Yii::$app->request->setBodyParams(['propertyId' => 1]);
        $this->controller()->actionAddProperty(1);
        $this->controller()->actionAddProperty(2);
        $this->assertTrue(Verse::findOne(1)->public);
        $this->assertTrue(Verse::findOne(2)->public);
        $this->assertSame(2, $this->controller()->actionDeletePropertyGlobally(1, 1)['affectedSceneCount']);
        $this->assertFalse(Verse::findOne(1)->public);
        $this->assertFalse(Verse::findOne(2)->public);
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
        $this->assertSame(['other-scene'], array_column($this->controller()->actionProperties(2), 'key'));
        Yii::$app->request->setBodyParams(['key' => 'public']);
        $recreated = $this->controller()->actionAddProperty(1);
        $this->assertNotSame(1, $recreated['property']['id']);
        $this->assertSame($recreated, $this->controller()->actionAddProperty(1));
        $this->assertTrue(Verse::findOne(1)->public);
        $this->assertFalse(Verse::findOne(2)->public);
    }

    #[DataProvider('unknownGlobalPropertyActions')]
    public function testUnknownGlobalPropertyReturns404WithoutWrites(string $action): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->$action(1, 999);
    }

    public static function unknownGlobalPropertyActions(): array
    {
        return [['actionPropertyUsage'], ['actionDeletePropertyGlobally']];
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
        // Root metadata updates are an explicit exception; editing, deleting and publishing are not.
        return [['index'], ['delete'], ['update-code'], ['take-photo']];
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

        $this->controller()->actionRemoveProperty(1, 1);
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
        $this->passRouteFilter($controller, self::actionId($action));
        try {
            $this->callPropertyAction($controller, $action, 1);
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
        foreach (['admin' => 2, 'manager' => 3, 'author' => 42, 'ordinary' => 7, 'guest' => null] as $role => $id) {
            foreach (['actionProperties', 'actionPropertyOptions', 'actionAddProperty', 'actionRemoveProperty', 'actionPropertyUsage', 'actionDeletePropertyGlobally'] as $action) {
                if ($role === 'admin' && in_array($action, ['actionProperties', 'actionPropertyOptions'], true)) continue;
                $cases[$role . '-' . $action] = [$id, $action];
            }
        }
        return $cases;
    }

    public function testAdminReadsAcrossAuthorsButManagesExistingAssociationsOnlyOnOwnScene(): void
    {
        $this->actAs(2);
        $controller = $this->controller();
        foreach (['properties', 'property-options', 'add-property', 'remove-property'] as $action) {
            $this->passRouteFilter($controller, $action);
        }
        $this->assertSame(['checkin'], array_column($controller->actionProperties(1), 'key'));
        $this->assertCount(3, $controller->actionPropertyOptions(1));
        Yii::$app->db->createCommand()->update('verse', ['author_id' => 2], ['id' => 1])->execute();
        Yii::$app->request->setBodyParams(['propertyId' => 3]);
        $added = $controller->actionAddProperty(1);
        $this->assertSame(['success' => true, 'property' => ['id' => 3, 'key' => 'other-scene', 'info' => null]], $added);
        $this->assertSame($added, $controller->actionAddProperty(1));
        $this->assertSame($added, $controller->actionRemoveProperty(1, 3));
        $this->assertSame(['other-scene'], array_column($controller->actionProperties(2), 'key'));
        $this->assertCount(3, $controller->actionPropertyOptions(1));
        Yii::$app->request->setBodyParams(['propertyId' => 1]);
        $controller->actionAddProperty(1);
        $this->assertTrue(Verse::findOne(1)->public);
        $controller->actionRemoveProperty(1, 1);
        $this->assertFalse(Verse::findOne(1)->public);
    }

    public function testAdminCannotAddOrRemoveExistingPropertyOnAnotherAuthorsNonEditableScene(): void
    {
        $this->actAs(2);
        $before = Yii::$app->db->createCommand('SELECT * FROM verse_property ORDER BY id')->queryAll();
        Yii::$app->request->setBodyParams(['propertyId' => 3]);
        foreach ([fn () => $this->controller()->actionAddProperty(1),
            fn () => $this->controller()->actionRemoveProperty(1, 2)] as $operation) {
            try {
                $operation();
                $this->fail('Only root may write properties across authors without scene editing permission');
            } catch (ForbiddenHttpException $error) {
                $this->assertSame(403, $error->statusCode);
                $this->assertSame($before, Yii::$app->db->createCommand('SELECT * FROM verse_property ORDER BY id')->queryAll());
            }
        }
        $this->assertSame(['checkin'], array_column($this->controller()->actionProperties(1), 'key'));
        $this->assertCount(3, $this->controller()->actionPropertyOptions(1));
    }

    public function testAdminCollaboratorCanMaintainExistingSceneProperties(): void
    {
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('admin'), 22);
        $this->actAs(22);
        Yii::$app->db->createCommand()->insert('group_verse', ['group_id' => 7, 'verse_id' => 2])->execute();
        Yii::$app->db->createCommand()->insert('group_user', ['group_id' => 7, 'user_id' => 22])->execute();
        $this->assertTrue(Verse::findOne(2)->editable);
        Yii::$app->request->setBodyParams(['propertyId' => 2]);
        $added = $this->controller()->actionAddProperty(2);
        $this->assertTrue($added['success']);
        $this->assertSame($added, $this->controller()->actionRemoveProperty(2, 2));
        $this->assertSame(['other-scene'], array_column($this->controller()->actionProperties(2), 'key'));
    }

    #[DataProvider('adminKeyBodies')]
    public function testAdminCannotCreateOrReuseDictionaryByKey(array $body): void
    {
        $this->actAs(2);
        Yii::$app->request->setBodyParams($body);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionAddProperty(1);
    }

    public static function adminKeyBodies(): array
    {
        return [[['key' => 'new']], [['key' => 'public']], [['propertyId' => 1, 'key' => 'public']],
            [['propertyId' => 1, 'info' => 'changed']]];
    }

    #[DataProvider('nonManagerIds')]
    public function testManagerAndOrdinaryActorsCannotAssociateExistingProperty(?int $id): void
    {
        $this->actAs($id);
        Yii::$app->request->setBodyParams(['propertyId' => 1]);
        $this->expectException(ForbiddenHttpException::class);
        $this->controller()->actionAddProperty(1);
    }

    public static function nonManagerIds(): array
    {
        return [[3], [42], [7], [null]];
    }

    public function testLegacyPublicWriteActionsDoNotExist(): void
    {
        $controller = $this->controller();
        $this->assertNull($controller->createAction('add-public'));
        $this->assertNull($controller->createAction('remove-public'));
        $this->assertFalse(method_exists($controller, 'actionAddPublic'));
        $this->assertFalse(method_exists($controller, 'actionRemovePublic'));
    }

    #[DataProvider('nonManagerIds')]
    public function testSceneSerializerDoesNotExposePropertyDetailsEvenWhenExplicitlyRequested(?int $id): void
    {
        $this->actAs($id);
        $scene = Verse::findOne(1);
        Yii::$app->db->createCommand()->insert('verse_property', ['verse_id' => 1, 'property_id' => 1])->execute();
        $serialized = $scene->toArray(['id'], ['public', 'properties', 'properties.info',
            'verseProperties', 'verseProperties.property', 'verseProperties.property.info']);
        $this->assertSame(['id' => 1, 'public' => true], $serialized);
        $this->assertNotContains('properties', $scene->extraFields());
        $this->assertNotContains('verseProperties', $scene->extraFields());
        $this->assertArrayNotHasKey('properties', $scene->fields());
        $this->assertArrayNotHasKey('verseProperties', $scene->fields());
    }

    public function testRootGlobalCatalogNeedsNoSceneAndIncludesUnusedProperties(): void
    {
        $this->passRouteFilter($this->controller(), 'global-property-options');
        Yii::$app->db->createCommand()->delete('verse_property')->execute();
        Yii::$app->db->createCommand()->delete('verse')->execute();
        $this->assertSame(['checkin', 'other-scene', 'public'],
            array_column($this->controller()->actionGlobalPropertyOptions(), 'key'));
    }

    public function testRootGlobalPropertyQueryIncludesPrivateScenesAcrossAuthorsWithoutDuplicatesAndKeepsMineBoundary(): void
    {
        Yii::$app->db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'], [[1, 2], [2, 2]])->execute();
        Yii::$app->request->setQueryParams(['propertyId' => '2']);
        $this->passRouteFilter($this->controller(), 'by-property');
        $global = $this->controller()->actionByProperty();
        $this->assertSame([1, 2], array_map('intval', $global->query->orderBy('id')->select('id')->column()));
        $this->assertSame(2, $global->getTotalCount());
        $this->assertSame([], $this->controller()->actionIndex()->query->select('id')->column());
        $this->passRouteFilter($this->controller(), 'by-property-scene');
        $this->assertSame(1, (int) $this->controller()->actionByPropertyScene(1)->id);
        Yii::$app->request->setQueryParams(['propertyId' => '1']);
        $this->assertSame([], $this->controller()->actionByProperty()->getModels());
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionByPropertyScene(1);
    }

    public function testGlobalPropertyQueryReusesSearchSortAndPagination(): void
    {
        Yii::$app->db->createCommand()->update('verse', ['name' => 'First scene'], ['id' => 1])->execute();
        Yii::$app->db->createCommand()->update('verse', ['name' => 'Second scene'], ['id' => 2])->execute();
        Yii::$app->db->createCommand()->insert('verse_property', ['verse_id' => 2, 'property_id' => 2])->execute();
        Yii::$app->request->setQueryParams(['propertyId' => '2', 'sort' => '-id', 'page' => '1', 'per-page' => '1']);
        $provider = $this->controller()->actionByProperty();
        // PHPUnit shares the automatic provider ID counter across requests.
        $provider->id = null;
        $provider->pagination->params = Yii::$app->request->queryParams;
        $provider->sort->params = Yii::$app->request->queryParams;
        $this->assertSame(2, $provider->getTotalCount());
        $this->assertSame([2], array_map(static fn(Verse $scene): int => (int) $scene->id, $provider->getModels()));
        Yii::$app->request->setQueryParams(['propertyId' => '2', 'VerseSearch' => ['name' => 'First']]);
        $this->assertSame([1], array_map(static fn(Verse $scene): int => (int) $scene->id,
            $this->controller()->actionByProperty()->getModels()));
    }

    #[DataProvider('invalidGlobalPropertyIds')]
    public function testGlobalPropertyQueryRejectsInvalidId($value): void
    {
        Yii::$app->request->setQueryParams($value === null ? [] : ['propertyId' => $value]);
        $this->expectException(BadRequestHttpException::class);
        $this->controller()->actionByProperty();
    }

    public static function invalidGlobalPropertyIds(): array
    {
        return [[null], [''], ['0'], ['-1'], ['1.5'], [' 1'], ['+1'], [['1']], [true], ['999999999999999999999999']];
    }

    public function testGlobalPropertyQueryRejectsUnknownProperty(): void
    {
        Yii::$app->request->setQueryParams(['propertyId' => '999']);
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->actionByProperty();
    }

    #[DataProvider('globalPropertyActors')]
    public function testGlobalPropertyReadActionsRequireRoot(?int $id, string $action): void
    {
        $this->actAs($id);
        Yii::$app->request->setQueryParams(['propertyId' => '2']);
        $this->expectException(ForbiddenHttpException::class);
        $action === 'actionByPropertyScene' ? $this->controller()->$action(1) : $this->controller()->$action();
    }

    public static function globalPropertyActors(): array
    {
        $cases = [];
        foreach ([2, 3, 42, null] as $id) {
            foreach (['actionGlobalPropertyOptions', 'actionByProperty', 'actionByPropertyScene'] as $action) {
                $cases[] = [$id, $action];
            }
        }
        return $cases;
    }

    #[DataProvider('globalPropertyActionIds')]
    public function testGlobalPropertyReadActionsRequireJwt(string $id): void
    {
        $this->actAs(null);
        $controller = $this->controller();
        $auth = Yii::createObject($controller->behaviors()['authenticator'] + [
            'user' => Yii::$app->user, 'request' => Yii::$app->request, 'response' => Yii::$app->response,
        ]);
        $this->expectException(UnauthorizedHttpException::class);
        $auth->beforeAction($controller->createAction($id));
    }

    public static function globalPropertyActionIds(): array
    {
        return [['global-property-options'], ['by-property'], ['by-property-scene']];
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
            'null ID' => [['propertyId' => null]], 'zero ID' => [['propertyId' => 0]],
            'negative ID' => [['propertyId' => -1]], 'string ID' => [['propertyId' => '3']],
            'float ID' => [['propertyId' => 3.0]], 'boolean ID' => [['propertyId' => true]],
            'array ID' => [['propertyId' => [3]]], 'ID and key' => [['propertyId' => 3, 'key' => 'other-scene']],
            'ID and info' => [['propertyId' => 3, 'info' => null]],
        ];
    }

    #[DataProvider('propertyActions')]
    public function testMissingSceneReturns404WithoutCreatingDictionaryEntries(string $action): void
    {
        Yii::$app->request->setBodyParams(['key' => 'missing-scene']);
        try {
            $this->callPropertyAction($this->controller(), $action, 999);
            $this->fail('Missing scene should fail');
        } catch (NotFoundHttpException $error) {
            $this->assertSame(404, $error->statusCode);
            $this->assertSame(3, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM property')->queryScalar());
        }
    }

    #[DataProvider('propertyActions')]
    public function testPropertyActionsRequireJwt(string $action): void
    {
        $this->actAs(null);
        $controller = $this->controller();
        $auth = Yii::createObject($controller->behaviors()['authenticator'] + [
            'user' => Yii::$app->user, 'request' => Yii::$app->request, 'response' => Yii::$app->response,
        ]);
        $this->expectException(UnauthorizedHttpException::class);
        $auth->beforeAction(new InlineAction(self::actionId($action), $controller, $action));
    }

    public static function propertyActions(): array
    {
        return [['actionProperties'], ['actionPropertyOptions'], ['actionAddProperty'], ['actionRemoveProperty'],
            ['actionPropertyUsage'], ['actionDeletePropertyGlobally']];
    }

    #[DataProvider('disallowedMethods')]
    public function testActionsRejectOtherHttpMethods(string $action, string $method): void
    {
        Yii::$app->request->headers->set('X-Http-Method-Override', $method);
        Yii::$app->request->setBodyParams(['key' => 'wrong-method']);
        $this->expectException(MethodNotAllowedHttpException::class);
        $this->controller()->runAction($action, ['id' => 1, 'propertyId' => 2]);
    }

    public static function disallowedMethods(): array
    {
        return [['add-property', 'GET'], ['properties', 'POST'], ['add-property', 'DELETE'], ['property-options', 'POST'],
            ['remove-property', 'GET'], ['remove-property', 'POST'], ['remove-property', 'PUT'],
            ['property-usage', 'POST'], ['property-usage', 'DELETE'], ['delete-property-globally', 'GET'],
            ['delete-property-globally', 'POST'], ['delete-property-globally', 'PUT'], ['by-property', 'POST'],
            ['by-property', 'DELETE'], ['global-property-options', 'POST'], ['by-property-scene', 'POST']];
    }

    public function testRoutesResolvePropertyMethodsAndPreflightFromSourceConfig(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/api/config/main.php';
        $rules = array_values(array_filter($config['components']['urlManager']['rules'], static function ($rule): bool {
            return is_array($rule) && ($rule['controller'] ?? null) === 'v1/verse';
        }));
        $manager = new UrlManager(['enablePrettyUrl' => true, 'showScriptName' => false, 'enableStrictParsing' => true, 'rules' => $rules]);
        foreach (['by-property', 'global-property-options', 'by-property/2'] as $path) {
            foreach (['GET', 'OPTIONS'] as $method) {
                $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
                $request->setPathInfo('v1/verses/' . $path);
                $request->headers->set('X-Http-Method-Override', $method);
                $expected = $method === 'OPTIONS' ? 'options' : ($path === 'by-property/2' ? 'by-property-scene' : $path);
                $this->assertSame(['v1/verse/' . $expected, $path === 'by-property/2' ? ['id' => '2'] : []], $manager->parseRequest($request));
            }
        }
        foreach (['POST', 'DELETE'] as $method) {
            $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
            $request->setPathInfo('v1/verses/2/public');
            $request->headers->set('X-Http-Method-Override', $method);
            $this->assertFalse($manager->parseRequest($request));
        }
        foreach (['GET' => 'properties', 'POST' => 'add-property', 'OPTIONS' => 'options'] as $method => $action) {
            $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
            $request->setPathInfo('v1/verses/2/properties');
            $request->headers->set('X-Http-Method-Override', $method);
            $this->assertSame(['v1/verse/' . $action, ['id' => '2']], $manager->parseRequest($request));
        }
        foreach (['GET' => 'property-options', 'OPTIONS' => 'options'] as $method => $action) {
            $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
            $request->setPathInfo('v1/verses/2/property-options');
            $request->headers->set('X-Http-Method-Override', $method);
            $this->assertSame(['v1/verse/' . $action, ['id' => '2']], $manager->parseRequest($request));
        }
        foreach (['DELETE' => 'remove-property', 'OPTIONS' => 'options'] as $method => $action) {
            $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
            $request->setPathInfo('v1/verses/2/properties/3');
            $request->headers->set('X-Http-Method-Override', $method);
            $this->assertSame(['v1/verse/' . $action, ['id' => '2', 'propertyId' => '3']], $manager->parseRequest($request));
        }
        foreach (['usage' => ['GET' => 'property-usage', 'OPTIONS' => 'options'],
            'global' => ['DELETE' => 'delete-property-globally', 'OPTIONS' => 'options']] as $suffix => $methods) {
            foreach ($methods as $method => $action) {
                $request = new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']);
                $request->setPathInfo('v1/verses/2/properties/3/' . $suffix);
                $request->headers->set('X-Http-Method-Override', $method);
                $this->assertSame(['v1/verse/' . $action, ['id' => '2', 'propertyId' => '3']], $manager->parseRequest($request));
            }
        }
    }

    private static function actionId(string $action): string
    {
        return match ($action) {
            'actionProperties' => 'properties',
            'actionPropertyOptions' => 'property-options',
            'actionRemoveProperty' => 'remove-property',
            'actionPropertyUsage' => 'property-usage',
            'actionDeletePropertyGlobally' => 'delete-property-globally',
            default => 'add-property',
        };
    }

    private function callPropertyAction(VerseController $controller, string $action, int $sceneId): array
    {
        return in_array($action, ['actionRemoveProperty', 'actionPropertyUsage', 'actionDeletePropertyGlobally'], true)
            ? $controller->$action($sceneId, 2)
            : $controller->$action($sceneId);
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
