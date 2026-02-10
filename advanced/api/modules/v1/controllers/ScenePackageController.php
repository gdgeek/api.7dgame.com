<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\File;
use api\modules\v1\models\Verse;
use api\modules\v1\services\ScenePackageService;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use OpenApi\Annotations as OA;

/**
 * ScenePackageController handles scene package export and import operations.
 *
 * - GET /v1/verses/{id}/export - Export a scene as JSON or ZIP
 * - POST /v1/verses/import - Import a scene from JSON or ZIP
 *
 * @OA\Tag(
 *     name="ScenePackage",
 *     description="场景包导出/导入接口"
 * )
 */
class ScenePackageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }

    /**
     * GET /v1/verses/{id}/export
     *
     * Export a scene as JSON or ZIP based on the Accept header.
     *
     * @OA\Get(
     *     path="/v1/scene-package/verses/{id}/export",
     *     summary="导出场景包",
     *     description="根据 Accept 头返回 JSON 或 ZIP 格式的完整场景数据树（Scene_Data_Tree），包含 verse、metas、resources、metaResourceLinks",
     *     tags={"ScenePackage"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="场景 Verse ID",
     *         @OA\Schema(type="integer", example=626)
     *     ),
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=false,
     *         description="响应格式：application/json（默认）或 application/zip",
     *         @OA\Schema(type="string", enum={"application/json", "application/zip"}, default="application/json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="导出成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="verse", type="object", description="场景数据",
     *                 @OA\Property(property="id", type="integer", example=626),
     *                 @OA\Property(property="name", type="string", example="我的场景"),
     *                 @OA\Property(property="uuid", type="string", example="verse-uuid-xxx"),
     *                 @OA\Property(property="version", type="integer", example=3),
     *                 @OA\Property(property="data", type="object", description="场景 JSON 数据"),
     *                 @OA\Property(property="verseCode", type="object",
     *                     @OA\Property(property="blockly", type="string"),
     *                     @OA\Property(property="lua", type="string"),
     *                     @OA\Property(property="js", type="string")
     *                 ),
     *                 @OA\Property(property="image", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="filename", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="metas", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="uuid", type="string"),
     *                     @OA\Property(property="title", type="string", example="实体A"),
     *                     @OA\Property(property="data", type="object"),
     *                     @OA\Property(property="events", type="object"),
     *                     @OA\Property(property="metaCode", type="object", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="resources", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=201),
     *                     @OA\Property(property="uuid", type="string"),
     *                     @OA\Property(property="name", type="string", example="模型A"),
     *                     @OA\Property(property="type", type="string", example="polygen"),
     *                     @OA\Property(property="file", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="url", type="string"),
     *                         @OA\Property(property="filename", type="string"),
     *                         @OA\Property(property="size", type="integer")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="metaResourceLinks", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="meta_id", type="integer", example=101),
     *                     @OA\Property(property="resource_id", type="integer", example=201)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未认证"),
     *     @OA\Response(response=403, description="无权访问该场景"),
     *     @OA\Response(response=404, description="场景不存在")
     * )
     *
     * @param int $id Verse ID
     * @return mixed JSON array or ZIP file stream
     * @throws NotFoundHttpException if Verse not found
     * @throws ForbiddenHttpException if user has no view permission
     */
    public function actionExport(int $id): mixed
    {
        // Query Verse and check existence
        $verse = Verse::findOne($id);
        if ($verse === null) {
            throw new NotFoundHttpException('Verse not found');
        }

        // Check viewable permission
        if (!$verse->viewable) {
            throw new ForbiddenHttpException('You are not authorized to access this verse');
        }

        // Build export data via service
        $service = new ScenePackageService();
        $exportData = $service->buildExportData($id);

        // Check Accept header for format negotiation
        $acceptHeader = Yii::$app->request->getHeaders()->get('Accept', 'application/json');

        if (stripos($acceptHeader, 'application/zip') !== false) {
            // ZIP export - create ZIP with scene.json
            $response = Yii::$app->response;

            $tmpFile = tempnam(sys_get_temp_dir(), 'scene_export_');
            $zip = new \ZipArchive();

            if ($zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                @unlink($tmpFile);
                throw new ServerErrorHttpException('Failed to create ZIP file');
            }

            $zip->addFromString('scene.json', json_encode($exportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $zip->close();

            $zipContent = file_get_contents($tmpFile);
            @unlink($tmpFile);

            // Build filename from verse name
            $verseName = preg_replace('/[^a-zA-Z0-9_\-\x{4e00}-\x{9fff}]/u', '_', $exportData['verse']['name'] ?? 'scene');
            $filename = $verseName . '.zip';

            $response->format = Response::FORMAT_RAW;
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->content = $zipContent;

            return $response;
        }

        // Default: JSON response
        return $exportData;
    }

    /**
     * POST /v1/verses/import
     *
     * Import a scene from JSON request body or ZIP file upload.
     *
     * @OA\Post(
     *     path="/v1/scene-package/verses/import",
     *     summary="导入场景包",
     *     description="通过 JSON 请求体或 ZIP 文件上传导入完整场景。JSON 模式使用 application/json，ZIP 模式使用 multipart/form-data 上传包含 scene.json 的 ZIP 文件。",
     *     tags={"ScenePackage"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="场景导入数据（JSON 格式）",
     *         @OA\JsonContent(
     *             required={"verse"},
     *             @OA\Property(property="verse", type="object", required={"name", "data", "version", "uuid"},
     *                 @OA\Property(property="name", type="string", description="场景名称", example="我的场景"),
     *                 @OA\Property(property="description", type="string", description="场景描述", example="这是一个测试场景"),
     *                 @OA\Property(property="data", type="string", description="场景 JSON 数据字符串", example="{\"type\":\"Verse\",\"children\":{}}"),
     *                 @OA\Property(property="version", type="integer", description="版本号", example=3),
     *                 @OA\Property(property="uuid", type="string", description="原始 UUID（导入时会生成新 UUID）", example="original-verse-uuid"),
     *                 @OA\Property(property="verseCode", type="object",
     *                     @OA\Property(property="blockly", type="string"),
     *                     @OA\Property(property="lua", type="string"),
     *                     @OA\Property(property="js", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="metas", type="array",
     *                 @OA\Items(type="object", required={"title", "uuid"},
     *                     @OA\Property(property="title", type="string", description="实体名称", example="实体A"),
     *                     @OA\Property(property="uuid", type="string", description="原始 UUID", example="original-meta-uuid"),
     *                     @OA\Property(property="data", type="string", description="实体 JSON 数据"),
     *                     @OA\Property(property="events", type="string", description="事件 JSON 数据"),
     *                     @OA\Property(property="metaCode", type="object",
     *                         @OA\Property(property="blockly", type="string"),
     *                         @OA\Property(property="lua", type="string")
     *                     ),
     *                     @OA\Property(property="resourceFileIds", type="array", description="关联的文件 ID 列表",
     *                         @OA\Items(type="integer", example=501)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="resourceFileMappings", type="array",
     *                 @OA\Items(type="object", required={"originalUuid", "fileId", "name", "type", "info"},
     *                     @OA\Property(property="originalUuid", type="string", description="原始资源 UUID", example="original-resource-uuid"),
     *                     @OA\Property(property="fileId", type="integer", description="已上传文件的 ID", example=501),
     *                     @OA\Property(property="name", type="string", description="资源名称", example="模型A"),
     *                     @OA\Property(property="type", type="string", description="资源类型", example="polygen"),
     *                     @OA\Property(property="info", type="string", description="资源信息 JSON", example="{\"size\":1024}")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="导入成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="verseId", type="integer", description="新创建的场景 ID", example=700),
     *             @OA\Property(property="metaIdMap", type="object", description="原始 Meta UUID → 新 Meta ID 映射，键为原始 UUID，值为新 Meta ID", example={"original-meta-uuid": 150}),
     *             @OA\Property(property="resourceIdMap", type="object", description="原始 Resource UUID → 新 Resource ID 映射，键为原始 UUID，值为新 Resource ID", example={"original-resource-uuid": 250})
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求数据验证失败（缺少必填字段、无效 ZIP 等）"),
     *     @OA\Response(response=401, description="未认证"),
     *     @OA\Response(response=422, description="fileId 引用的文件不存在"),
     *     @OA\Response(response=500, description="导入事务失败")
     * )
     *
     * @return array Import result with verseId, metaIdMap, resourceIdMap
     * @throws BadRequestHttpException if validation fails
     * @throws UnprocessableEntityHttpException if fileId references non-existent File
     * @throws ServerErrorHttpException if import transaction fails
     */
    public function actionImport(): array
    {
        $contentType = Yii::$app->request->getContentType();

        // Parse request data based on Content-Type
        if (stripos($contentType, 'multipart/form-data') !== false) {
            // ZIP mode: extract and parse scene.json from uploaded ZIP
            $data = $this->parseZipUpload();
        } else {
            // JSON mode: parse JSON request body
            $data = Yii::$app->request->getBodyParams();
        }

        // Validate required fields
        $this->validateImportData($data);

        // Validate fileId existence
        $this->validateFileIds($data);

        // Call ScenePackageService to import
        try {
            $service = new ScenePackageService();
            $result = $service->importScene($data);
            return $result;
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse ZIP file upload and extract scene.json data.
     *
     * @return array Parsed scene data from scene.json
     * @throws BadRequestHttpException if ZIP is invalid or missing scene.json
     */
    private function parseZipUpload(): array
    {
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');

        if ($uploadedFile === null || $uploadedFile->error !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException('Invalid ZIP file');
        }

        $zip = new \ZipArchive();
        $openResult = $zip->open($uploadedFile->tempName);

        if ($openResult !== true) {
            throw new BadRequestHttpException('Invalid ZIP file');
        }

        $sceneJson = $zip->getFromName('scene.json');
        $zip->close();

        if ($sceneJson === false) {
            throw new BadRequestHttpException('ZIP file does not contain scene.json');
        }

        $data = json_decode($sceneJson, true);

        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON in scene.json');
        }

        return $data;
    }

    /**
     * Validate required fields in import data.
     *
     * @param array|null $data Import data to validate
     * @throws BadRequestHttpException if required fields are missing
     */
    private function validateImportData($data): void
    {
        if (!is_array($data) || empty($data)) {
            throw new BadRequestHttpException('Missing required field: verse');
        }

        // verse object must exist
        if (!isset($data['verse']) || !is_array($data['verse'])) {
            throw new BadRequestHttpException('Missing required field: verse');
        }

        // verse required fields
        $verseRequiredFields = ['name', 'data', 'version', 'uuid'];
        foreach ($verseRequiredFields as $field) {
            if (!isset($data['verse'][$field]) || $data['verse'][$field] === '') {
                throw new BadRequestHttpException("Missing required field: verse.{$field}");
            }
        }

        // metas[] required fields (if present)
        if (isset($data['metas']) && is_array($data['metas'])) {
            $metaRequiredFields = ['title', 'uuid'];
            foreach ($data['metas'] as $index => $meta) {
                foreach ($metaRequiredFields as $field) {
                    if (!isset($meta[$field]) || $meta[$field] === '') {
                        throw new BadRequestHttpException("Missing required field: metas[{$index}].{$field}");
                    }
                }
            }
        }

        // resourceFileMappings[] required fields (if present)
        if (isset($data['resourceFileMappings']) && is_array($data['resourceFileMappings'])) {
            $mappingRequiredFields = ['originalUuid', 'fileId', 'name', 'type', 'info'];
            foreach ($data['resourceFileMappings'] as $index => $mapping) {
                foreach ($mappingRequiredFields as $field) {
                    if (!isset($mapping[$field]) || $mapping[$field] === '') {
                        throw new BadRequestHttpException("Missing required field: resourceFileMappings[{$index}].{$field}");
                    }
                }
            }
        }
    }

    /**
     * Validate that all fileId references in resourceFileMappings exist in the File table.
     *
     * @param array $data Import data containing resourceFileMappings
     * @throws UnprocessableEntityHttpException if a fileId does not exist
     */
    private function validateFileIds(array $data): void
    {
        if (!isset($data['resourceFileMappings']) || !is_array($data['resourceFileMappings'])) {
            return;
        }

        foreach ($data['resourceFileMappings'] as $mapping) {
            $fileId = $mapping['fileId'];
            $file = File::findOne($fileId);
            if ($file === null) {
                throw new UnprocessableEntityHttpException("File not found for fileId: {$fileId}");
            }
        }
    }
}
