<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use OpenApi\Generator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="AR创作平台 API",
 *     description="AR创作平台后端 API 文档 - 提供完整的用户认证、资源管理、教育管理等功能接口"
 * )
 * @OA\Server(url="/", description="API Server")
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="使用 JWT Bearer Token 进行认证"
 * )
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="错误响应",
 *     description="标准错误响应格式",
 *     required={"name", "message", "code", "status"},
 *     @OA\Property(property="name", type="string", description="错误名称", example="Bad Request"),
 *     @OA\Property(property="message", type="string", description="错误详细消息", example="Invalid input data"),
 *     @OA\Property(property="code", type="integer", description="应用错误码", example=0),
 *     @OA\Property(property="status", type="integer", description="HTTP状态码", example=400)
 * )
 */
class SwaggerController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * 在执行任何操作前进行身份验证
     * 使用 HTTP Basic Authentication 保护 Swagger 文档
     * 凭据配置在 config/params.php 的 'swagger' 键中
     */
    public function beforeAction($action)
    {
        // 从配置文件读取凭据
        $swaggerConfig = Yii::$app->params['swagger'] ?? null;

        if (!$swaggerConfig) {
            throw new \yii\web\ServerErrorHttpException('Swagger 配置未找到');
        }

        // 检查是否启用
        if (!$swaggerConfig['enabled']) {
            throw new \yii\web\NotFoundHttpException('API documentation is disabled');
        }

        // 检查 HTTP Basic Auth 凭据
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        if ($username !== $swaggerConfig['username'] || $password !== $swaggerConfig['password']) {
            header('WWW-Authenticate: Basic realm="Swagger API Documentation"');
            header('HTTP/1.0 401 Unauthorized');
            echo '需要认证才能访问 API 文档';
            exit;
        }

        return parent::beforeAction($action);
    }

    /**
     * 渲染 Swagger UI 界面
     */
    public function actionIndex()
    {
        $swaggerUiUrl = Yii::$app->request->baseUrl . '/swagger-ui';
        $jsonSchemaUrl = Yii::$app->urlManager->createUrl(['swagger/json-schema']);

        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 文档 - Swagger UI</title>
    <link rel="stylesheet" href="{$swaggerUiUrl}/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{$swaggerUiUrl}/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: "{$jsonSchemaUrl}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
HTML;
    }

    /**
     * 生成 OpenAPI JSON Schema
     */
    public function actionJsonSchema()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $baseDir = dirname(__DIR__); // api/

        // 自动扫描所有控制器文件
        $scanFiles = [
            $baseDir . '/controllers/SwaggerController.php',
        ];
        
        // 排除列表：在路由配置中被注释掉的控制器
        $excludedControllers = [
            'SiteController.php',    // 根控制器 site，已在 main.php 中注释
            'ServerController.php',  // 根控制器 server，已在 main.php 中注释
        ];
        
        // 扫描 api/controllers 目录
        $apiControllers = glob($baseDir . '/controllers/*Controller.php');
        if ($apiControllers) {
            // 过滤掉被排除的控制器
            $apiControllers = array_filter($apiControllers, function($file) use ($excludedControllers) {
                $basename = basename($file);
                return !in_array($basename, $excludedControllers);
            });
            $scanFiles = array_merge($scanFiles, $apiControllers);
        }
        
        // 扫描 api/modules/v1/controllers 目录
        $v1Controllers = glob($baseDir . '/modules/v1/controllers/*Controller.php');
        if ($v1Controllers) {
            $scanFiles = array_merge($scanFiles, $v1Controllers);
        }
        
        // 扫描 api/modules/v1/models 目录
        $v1Models = glob($baseDir . '/modules/v1/models/*.php');
        if ($v1Models) {
            // 过滤掉非模型文件（如 data 目录下的文件）
            $v1Models = array_filter($v1Models, function($file) {
                return !strpos($file, '/data/') && basename($file) !== 'Module.php';
            });
            $scanFiles = array_merge($scanFiles, $v1Models);
        }

        // 文件存在性检查（已经通过 glob 确保存在）
        $existingFiles = array_filter($scanFiles, 'file_exists');

        if (empty($existingFiles)) {
            return [
                'error' => 'No files found to scan',
                'baseDir' => $baseDir,
                'attempted' => $scanFiles,
                'existing' => $existingFiles
            ];
        }

        try {
            $openapi = Generator::scan($existingFiles, ['validate' => false]);
            return json_decode($openapi->toJson());
        } catch (\Exception $e) {
            Yii::error('OpenAPI generation failed: ' . $e->getMessage());
            return [
                'error' => 'Failed to generate OpenAPI schema',
                'message' => YII_DEBUG ? $e->getMessage() : 'Internal error',
                'trace' => YII_DEBUG ? $e->getTraceAsString() : null
            ];
        }
    }
}
