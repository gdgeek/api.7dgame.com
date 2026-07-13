<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\ScenePackageController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;
use yii\web\UnprocessableEntityHttpException;

final class ScenePackageFileOwnershipTest extends TestCase
{
    private $originalDbComponent;
    private $originalUserComponent;
    private ScenePackageController $controller;
    private \ReflectionMethod $validateFileIds;

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
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%file}}', ['id', 'user_id'], [
            [100, 42],
            [200, 7],
        ])->execute();
        Yii::$app->set('user', new ScenePackageFileOwnershipTestUser(42));

        $this->controller = new ScenePackageController('scene-package', Yii::$app->getModule('v1'));
        $this->validateFileIds = new \ReflectionMethod(ScenePackageController::class, 'validateFileIds');
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);

        parent::tearDown();
    }

    public function testAcceptsFileOwnedByCurrentUser(): void
    {
        $this->validateFileIds->invoke($this->controller, $this->mappingData(100));

        $this->assertTrue(true);
    }

    public function testRejectsFileOwnedByAnotherUser(): void
    {
        $this->expectException(ForbiddenHttpException::class);
        $this->expectExceptionMessage('200');

        $this->validateFileIds->invoke($this->controller, $this->mappingData(200));
    }

    public function testStillRejectsMissingFile(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('300');

        $this->validateFileIds->invoke($this->controller, $this->mappingData(300));
    }

    private function mappingData(int $fileId): array
    {
        return [
            'resourceFileMappings' => [
                ['fileId' => $fileId],
            ],
        ];
    }
}

final class ScenePackageFileOwnershipTestUser extends Component
{
    public function __construct(public int $id, $config = [])
    {
        parent::__construct($config);
    }
}
