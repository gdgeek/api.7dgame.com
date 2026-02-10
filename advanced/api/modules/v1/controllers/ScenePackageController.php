<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\File;
use api\modules\v1\models\Verse;
use api\modules\v1\models\Version;
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

        // Allow export action to bypass content negotiation (supports application/zip)
        $behaviors['contentNegotiator']['except'] = ['export'];

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

            $filename = 'scene_' . $id . '.zip';

            $response->on(Response::EVENT_AFTER_SEND, function () use ($tmpFile) {
                @unlink($tmpFile);
            });

            return $response->sendFile($tmpFile, $filename, [
                'mimeType' => 'application/zip',
                'inline' => false,
            ]);
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
     *     description="通过 JSON 请求体或 ZIP 文件上传导入完整场景。JSON 模式使用 application/json Content-Type 提交场景数据；ZIP 模式使用 multipart/form-data 上传包含 scene.json 的 ZIP 文件。请求体需包含 verse（必填 name/data/uuid）、metas 数组（必填 title/uuid）、resourceFileMappings 数组（必填 originalUuid/fileId/name/type/info）。",
     *     tags={"ScenePackage"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="场景导入数据（JSON 格式），verse 必填字段：name, data, uuid",
     *         @OA\JsonContent(
     *             required={"verse"},
     *             @OA\Property(property="verse", type="object", description="场景数据，必填：name, data, uuid"),
     *             @OA\Property(property="metas", type="array", description="实体数组，每项必填：title, uuid",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(property="resourceFileMappings", type="array", description="资源文件映射数组，每项必填：originalUuid, fileId, name, type, info",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="导入成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="verseId", type="integer", description="新创建的场景 ID", example=700),
     *             @OA\Property(property="metaIdMap", type="object", description="原始 Meta UUID 到新 Meta ID 的映射"),
     *             @OA\Property(property="resourceIdMap", type="object", description="原始 Resource UUID 到新 Resource ID 的映射")
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

        // Validate version compatibility
        $this->validateVersion($data);

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
     * Validate version compatibility of import data.
     *
     * @param array|null $data Import data to validate
     * @throws BadRequestHttpException if version is missing or mismatched
     */
    private function validateVersion($data): void
    {
        $currentVersion = Version::getCurrentVersionNumber();
        $packageVersion = (int) ($data['verse']['version'] ?? 0);

        if ($packageVersion !== $currentVersion) {
            throw new BadRequestHttpException(
                "Version mismatch: package version is {$packageVersion}, but current system version is {$currentVersion}"
            );
        }
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
