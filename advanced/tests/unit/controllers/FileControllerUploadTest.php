<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\FileController;
use PHPUnit\Framework\TestCase;

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

    /**
     * Test that the controller keeps the default 'create' action.
     * POST /v1/files creates a File database record (url/filename/key from COS).
     */
    public function testCreateActionIsPresent()
    {
        $actions = $this->controller->actions();
        $this->assertArrayHasKey('create', $actions);
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
}
