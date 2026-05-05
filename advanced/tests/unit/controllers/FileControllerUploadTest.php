<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\FileController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\Request;
use yii\web\Response;

/**
 * FileController 单元测试
 *
 * 测试 FileController 的配置：
 * - 保留默认 create action（用于创建 File 数据库记录）
 * - model class 正确设置
 *
 * 注意：FileController 不负责文件上传，仅管理 file 表的数据库记录。
 * 文件上传通过腾讯云 COS 直传完成。
 *
 * @group file-controller
 */
class FileControllerUploadTest extends TestCase
{
    /**
     * @var FileController
     */
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new FileController('file', \Yii::$app);
    }

    public function testCreateActionIsInline()
    {
        $actions = $this->controller->actions();
        $this->assertArrayNotHasKey('create', $actions);
        $this->assertNotNull($this->controller->createAction('create'));
    }

    /**
     * Test that the controller model class is correctly set.
     */
    public function testModelClassIsSet()
    {
        $this->assertEquals('api\modules\v1\models\File', $this->controller->modelClass);
    }

    /**
     * Test that the controller has rate limiter behavior.
     */
    public function testHasRateLimiterBehavior()
    {
        $behaviors = $this->controller->behaviors();
        $this->assertArrayHasKey('rateLimiter', $behaviors);
    }

    public function testCreateReusesExistingFileRecordForSameUserAndKey(): void
    {
        $restore = $this->useFileCreateEnvironment(42);
        try {
            Yii::$app->db->createCommand()->insert('{{%file}}', [
                'id' => 1,
                'md5' => 'mesh-md5',
                'type' => 'model/gltf-binary',
                'url' => 'https://cos.example.com/spaces/zip-a/mesh.glb',
                'user_id' => 42,
                'created_at' => '2026-05-06 00:00:00',
                'filename' => 'mesh.glb',
                'size' => 123,
                'key' => 'spaces/zip-a/mesh.glb',
            ])->execute();
            Yii::$app->request->setBodyParams([
                'md5' => 'mesh-md5',
                'type' => 'model/gltf-binary',
                'url' => 'https://cos.example.com/spaces/zip-a/mesh.glb',
                'filename' => 'mesh.glb',
                'size' => 123,
                'key' => 'spaces/zip-a/mesh.glb',
            ]);

            $controller = new FileController('file', Yii::$app);
            Yii::$app->controller = $controller;
            $model = $controller->actionCreate();

            $this->assertSame(1, (int)$model->id);
            $this->assertSame(42, (int)$model->user_id);
            $this->assertSame(1, (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%file}}')->queryScalar());
            $this->assertSame(200, Yii::$app->response->statusCode);
        } finally {
            $restore();
        }
    }

    public function testCreateAllowsSeparateFileRecordForDifferentUserAndSameKey(): void
    {
        $restore = $this->useFileCreateEnvironment(42);
        try {
            Yii::$app->db->createCommand()->insert('{{%file}}', [
                'id' => 1,
                'md5' => 'mesh-md5',
                'type' => 'model/gltf-binary',
                'url' => 'https://cos.example.com/spaces/zip-a/mesh.glb',
                'user_id' => 7,
                'created_at' => '2026-05-06 00:00:00',
                'filename' => 'mesh.glb',
                'size' => 123,
                'key' => 'spaces/zip-a/mesh.glb',
            ])->execute();
            Yii::$app->request->setBodyParams([
                'md5' => 'mesh-md5',
                'type' => 'model/gltf-binary',
                'url' => 'https://cos.example.com/spaces/zip-a/mesh.glb',
                'filename' => 'mesh.glb',
                'size' => 123,
                'key' => 'spaces/zip-a/mesh.glb',
            ]);

            $controller = new FileController('file', Yii::$app);
            Yii::$app->controller = $controller;
            $model = $controller->actionCreate();

            $this->assertSame(42, (int)$model->user_id);
            $this->assertSame(2, (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%file}}')->queryScalar());
            $this->assertSame(201, Yii::$app->response->statusCode);
        } finally {
            $restore();
        }
    }

    private function useFileCreateEnvironment(int $userId): callable
    {
        $originalDbComponent = Yii::$app->get('db', false);
        $originalRequestComponent = Yii::$app->get('request', false);
        $originalResponseComponent = Yii::$app->get('response', false);
        $originalUserComponent = Yii::$app->get('user', false);
        $originalRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->pdo->sqliteCreateFunction('NOW', static fn () => gmdate('Y-m-d H:i:s'));
        Yii::$app->db->createCommand()->createTable('{{%user}}', [
            'id' => 'pk',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%file}}', [
            'id' => 'pk',
            'md5' => 'string not null',
            'type' => 'string',
            'url' => 'string',
            'user_id' => 'integer not null',
            'created_at' => 'datetime',
            'filename' => 'string',
            'size' => 'integer',
            'key' => 'string not null',
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%user}}', ['id'], [
            [7],
            [42],
        ])->execute();
        Yii::$app->set('user', new FileControllerUploadTestUser($userId));
        Yii::$app->set('request', new Request());
        Yii::$app->set('response', new Response());
        $_SERVER['REQUEST_METHOD'] = 'POST';

        return static function () use (
            $originalDbComponent,
            $originalRequestComponent,
            $originalResponseComponent,
            $originalUserComponent,
            $originalRequestMethod
        ): void {
            Yii::$app->set('request', $originalRequestComponent);
            Yii::$app->set('response', $originalResponseComponent);
            Yii::$app->set('user', $originalUserComponent);
            Yii::$app->db->close();
            Yii::$app->set('db', $originalDbComponent);
            if ($originalRequestMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
            }
        };
    }
}

final class FileControllerUploadTestUser extends Component
{
    public function __construct(public int $id, $config = [])
    {
        parent::__construct($config);
    }
}
