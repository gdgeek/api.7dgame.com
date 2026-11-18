<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\ResourceController;
use api\modules\v1\models\Resource;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;

final class ResourceFileOwnershipTest extends TestCase
{
    private $originalDbComponent;
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        $this->originalUserComponent = Yii::$app->get('user', false);

        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%file}}', [
            'id' => 'pk',
            'user_id' => 'integer not null',
            'key' => 'text not null',
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%file}}', ['id', 'user_id', 'key'], [
            [100, 42, 'ordinary-file'],
            [200, 7, 'another-file'],
            [300, 42, 'subtitles/42/private.json'],
        ])->execute();
        Yii::$app->set('user', new ResourceFileOwnershipTestUser());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testOwnedFileAndImageAreAccepted(): void
    {
        $controller = new TestableResourceController('resource', Yii::$app->getModule('v1'));
        $controller->requireOwnedFiles(new FileBackedResource([
            'file_id' => 100,
            'image_id' => 100,
        ]));

        $this->assertTrue(true);
    }

    public function testAnotherUsersFileIsRejected(): void
    {
        $controller = new TestableResourceController('resource', Yii::$app->getModule('v1'));

        $this->expectException(ForbiddenHttpException::class);
        $controller->requireOwnedFiles(new FileBackedResource([
            'file_id' => 200,
        ]));
    }

    public function testSubtitleVersionCannotBeReusedAsAnOrdinaryFile(): void
    {
        $controller = new TestableResourceController('resource', Yii::$app->getModule('v1'));
        $this->expectException(ForbiddenHttpException::class);
        $controller->requireOwnedFiles(new FileBackedResource(['file_id' => 300]));
    }
}

final class ResourceFileOwnershipTestUser extends Component
{
    public int $id = 42;
}

final class FileBackedResource extends Resource
{
    public function attributes(): array
    {
        return ['file_id', 'image_id', 'type'];
    }
}

final class TestableResourceController extends ResourceController
{
    public function requireOwnedFiles(Resource $model): void
    {
        $this->ensureOwnedFiles($model);
    }
}
