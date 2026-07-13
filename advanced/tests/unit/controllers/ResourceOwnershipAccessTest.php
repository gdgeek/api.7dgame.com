<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\FileController;
use api\modules\v1\controllers\MetaController;
use api\modules\v1\controllers\ResourceController;
use api\modules\v1\controllers\VerseController;
use api\modules\v1\models\File;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Resource;
use api\modules\v1\models\Verse;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

final class ResourceOwnershipAccessTest extends TestCase
{
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalUserComponent = Yii::$app->get('user', false);
        Yii::$app->set('user', new OwnershipAccessTestUser());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        parent::tearDown();
    }

    public function testResourceWriteAccessIsLimitedToOwner(): void
    {
        $controller = new ResourceController('resource', Yii::$app->getModule('v1'));
        $controller->checkAccess('update', new OwnershipResource(['author_id' => 42]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('delete', new OwnershipResource(['author_id' => 7]));
    }

    public function testResourceViewRemainsAvailableForSharedContent(): void
    {
        $controller = new ResourceController('resource', Yii::$app->getModule('v1'));
        $controller->checkAccess('view', new OwnershipResource(['author_id' => 7]));

        $this->assertTrue(true);
    }

    public function testFileAccessIsLimitedToOwner(): void
    {
        $controller = new FileController('file', Yii::$app->getModule('v1'));
        $controller->checkAccess('view', new OwnershipFile(['user_id' => 42]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('update', new OwnershipFile(['user_id' => 7]));
    }

    public function testFileViewRemainsAvailableForSharedContent(): void
    {
        $controller = new FileController('file', Yii::$app->getModule('v1'));
        $controller->checkAccess('view', new OwnershipFile(['user_id' => 7]));

        $this->assertTrue(true);
    }

    public function testMetaWriteAccessUsesEditablePermission(): void
    {
        $controller = new MetaController('meta', Yii::$app->getModule('v1'));
        $controller->checkAccess('view', new OwnershipMeta(['allowView' => true]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('update', new OwnershipMeta(['allowEdit' => false]));
    }

    public function testVerseViewAccessUsesVisibilityPermission(): void
    {
        $controller = new VerseController('verse', Yii::$app->getModule('v1'));
        $controller->checkAccess('update', new OwnershipVerse(['allowEdit' => true]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('view', new OwnershipVerse(['allowView' => false]));
    }
}

final class OwnershipAccessTestUser extends Component
{
    public int $id = 42;
    public object $identity;

    public function __construct($config = [])
    {
        $this->identity = (object) ['id' => $this->id];
        parent::__construct($config);
    }
}

final class OwnershipResource extends Resource
{
    public function attributes(): array
    {
        return ['author_id'];
    }
}

final class OwnershipFile extends File
{
    public function attributes(): array
    {
        return ['user_id'];
    }
}

final class OwnershipMeta extends Meta
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

final class OwnershipVerse extends Verse
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
