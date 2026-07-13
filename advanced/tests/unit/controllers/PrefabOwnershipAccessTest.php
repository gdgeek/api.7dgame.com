<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PrefabController;
use api\modules\v1\models\Meta;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

final class PrefabOwnershipAccessTest extends TestCase
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
        Yii::$app->db->createCommand()->createTable('{{%meta}}', [
            'id' => 'pk',
            'prefab' => 'integer not null default 0',
        ])->execute();
        Yii::$app->set('request', new Request());
        Yii::$app->set('response', new Response());
        Yii::$app->set('user', new PrefabOwnershipTestUser());
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

    public function testViewUsesMetaVisibilityPermission(): void
    {
        $controller = $this->createController();
        $controller->checkAccess('view', new PrefabPermissionMeta(['allowView' => true]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('view', new PrefabPermissionMeta(['allowView' => false]));
    }

    public function testUpdateUsesMetaEditPermission(): void
    {
        $controller = $this->createController();
        $controller->checkAccess('update', new PrefabPermissionMeta(['allowEdit' => true]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('update', new PrefabPermissionMeta(['allowEdit' => false]));
    }

    public function testDeleteUsesMetaEditPermission(): void
    {
        $controller = $this->createController();
        $controller->checkAccess('delete', new PrefabPermissionMeta(['allowEdit' => true]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('delete', new PrefabPermissionMeta(['allowEdit' => false]));
    }

    public function testMissingViewReturnsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->createController()->actionView(999);
    }

    public function testMissingUpdateReturnsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->createController()->actionUpdate(999);
    }

    public function testMissingDeleteReturnsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->createController()->actionDelete(999);
    }

    private function createController(): PrefabController
    {
        return new PrefabController('prefab', Yii::$app->getModule('v1'));
    }
}

final class PrefabOwnershipTestUser extends Component
{
    public int $id = 42;
    public object $identity;

    public function __construct($config = [])
    {
        $this->identity = (object) ['id' => $this->id];
        parent::__construct($config);
    }
}

final class PrefabPermissionMeta extends Meta
{
    public bool $allowView = false;
    public bool $allowEdit = false;

    public function attributes(): array
    {
        return [];
    }

    public function getViewable(): bool
    {
        return $this->allowView;
    }

    public function getEditable(): bool
    {
        return $this->allowEdit;
    }
}
