<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\SpaceController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\Response;

final class SpaceControllerTest extends TestCase
{
    private $originalDbComponent;
    private $originalRequestComponent;
    private $originalResponseComponent;
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        $this->originalRequestComponent = Yii::$app->get('request', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->originalUserComponent = Yii::$app->get('user', false);

        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%space}}', [
            'id' => 'pk',
            'name' => 'string not null',
            'user_id' => 'integer not null',
            'mesh_id' => 'integer not null',
            'image_id' => 'integer',
            'file_id' => 'integer',
            'created_at' => 'datetime',
            'data' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert(
            '{{%space}}',
            ['id', 'name', 'user_id', 'mesh_id', 'image_id', 'file_id', 'created_at', 'data'],
            [
                [1, 'Mine A', 42, 100, null, 1100, '2026-04-27 00:00:00', '{}'],
                [2, 'Mine B', 42, 101, null, 1101, '2026-04-27 00:00:00', '{}'],
                [3, 'Other', 7, 102, null, 1102, '2026-04-27 00:00:00', '{}'],
            ]
        )->execute();

        Yii::$app->set('user', new SpaceControllerTestUser());
        Yii::$app->set('request', new Request());
        Yii::$app->set('response', new Response());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('request', $this->originalRequestComponent);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testIndexOnlyReturnsCurrentUsersSpaces(): void
    {
        $controller = new SpaceController('space', Yii::$app->getModule('v1'));

        $dataProvider = $controller->actionIndex();
        $models = $dataProvider->getModels();
        $names = array_column($models, 'name');
        sort($names);

        $this->assertSame(['Mine A', 'Mine B'], $names);
    }

    public function testControllerUsesJwtAndAccessControl(): void
    {
        $controller = new SpaceController('space', Yii::$app->getModule('v1'));
        $behaviors = $controller->behaviors();

        $this->assertSame(\yii\filters\auth\CompositeAuth::class, $behaviors['authenticator']['class']);
        $this->assertContains(\bizley\jwt\JwtHttpBearerAuth::class, $behaviors['authenticator']['authMethods']);
        $this->assertSame(\mdm\admin\components\AccessControl::class, $behaviors['access']['class']);
    }

    public function testCheckAccessRejectsAnotherUsersSpace(): void
    {
        $controller = new SpaceController('space', Yii::$app->getModule('v1'));
        $space = \api\modules\v1\models\Space::findOne(3);

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('view', $space);
    }

    public function testCheckAccessAllowsCreateBeforeUserIdIsBlamed(): void
    {
        $controller = new SpaceController('space', Yii::$app->getModule('v1'));

        $controller->checkAccess('create', new \api\modules\v1\models\Space());
        $this->assertTrue(true);
    }
}

final class SpaceControllerTestUser extends Component
{
    public int $id = 42;
}
