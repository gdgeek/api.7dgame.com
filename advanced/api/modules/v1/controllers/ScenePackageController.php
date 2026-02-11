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
 * JSON endpoints (standard REST, handled by contentNegotiator):
 * - GET  /v1/scene-package/verses/{id}/export     → JSON export
 * - POST /v1/scene-package/verses/import           → JSON import
 *
 * ZIP endpoints (raw binary, excluded from contentNegotiator):
 * - GET  /v1/scene-package/verses/{id}/export-zip  → ZIP download
 * - POST /v1/scene-package/verses/import-zip       → ZIP upload
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

        // Accept any content type, default to JSON. Avoids 406 when clients
        // don't send Accept: application/json (e.g. Postman, browsers).
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
            'application/xml' => Response::FORMAT_XML,
            '*/*' => Response::FORMAT_JSON,
        ];
        // ZIP actions skip contentNegotiator entirely (raw binary response)
        $behaviors['contentNegotiator']['except'] = ['export-zip', 'import-zip'];

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


    // =========================================================================
    // Export: JSON
    // =========================================================================

    /**
     * GET /v1/scene-package/verses/{id}/export
     *
     * Export scene data tree as JSON (standard REST response).
     *
     * @param int $id Verse ID
     * @return array Scene_Data_Tree
     */
    public function actionExport(int $id): array
    {
        $this->findVerseOrFail($id);
        $service = new ScenePackageService();
        return $service->buildExportData($id);
    }

    // =========================================================================
    // Export: ZIP
    // =========================================================================

    /**
     * GET /v1/scene-package/verses/{id}/export-zip
     *
     * Export scene data tree as a ZIP file containing scene.json.
     *
     * @param int $id Verse ID
     * @return Response
     */
    public function actionExportZip(int $id): Response
    {
        $verse = $this->findVerseOrFail($id);
        $service = new ScenePackageService();
        $exportData = $service->buildExportData($id);

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

    // =========================================================================
    // Import: JSON
    // =========================================================================

    /**
     * POST /v1/scene-package/verses/import
     *
     * Import scene from JSON request body.
     *
     * @return array {verseId, metaIdMap, resourceIdMap}
     */
    public function actionImport(): array
    {
        $data = Yii::$app->request->getBodyParams();

        $this->validateImportData($data);
        $this->validateVersion($data);
        $this->validateFileIds($data);

        try {
            $service = new ScenePackageService();
            return $service->importScene($data);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Import failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Import: ZIP
    // =========================================================================

    /**
     * POST /v1/scene-package/verses/import-zip
     *
     * Import scene from uploaded ZIP file containing scene.json.
     *
     * @return array {verseId, metaIdMap, resourceIdMap}
     */
    public function actionImportZip(): array
    {
        // import-zip is excluded from contentNegotiator (for ZIP upload),
        // but the response is JSON, so set format manually.
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = $this->parseZipUpload();

        $this->validateImportData($data);
        $this->validateVersion($data);
        $this->validateFileIds($data);

        try {
            $service = new ScenePackageService();
            return $service->importScene($data);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException('Import failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Find Verse by ID, check existence and viewable permission.
     *
     * @param int $id
     * @return Verse
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    private function findVerseOrFail(int $id): Verse
    {
        $verse = Verse::findOne($id);
        if ($verse === null) {
            throw new NotFoundHttpException('Verse not found');
        }
        if (!$verse->viewable) {
            throw new ForbiddenHttpException('You are not authorized to access this verse');
        }
        return $verse;
    }

    /**
     * Parse ZIP file upload and extract scene.json data.
     *
     * @return array
     * @throws BadRequestHttpException
     */
    private function parseZipUpload(): array
    {
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');

        if ($uploadedFile === null || $uploadedFile->error !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException('Invalid ZIP file');
        }

        $zip = new \ZipArchive();
        if ($zip->open($uploadedFile->tempName) !== true) {
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
     * Validate version compatibility.
     *
     * @param array $data
     * @throws BadRequestHttpException
     */
    private function validateVersion(array $data): void
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
     * @param mixed $data
     * @throws BadRequestHttpException
     */
    private function validateImportData($data): void
    {
        if (!is_array($data) || empty($data)) {
            throw new BadRequestHttpException('Missing required field: verse');
        }

        if (!isset($data['verse']) || !is_array($data['verse'])) {
            throw new BadRequestHttpException('Missing required field: verse');
        }

        // Fields that must be present and non-empty
        $verseRequiredFields = ['name', 'uuid'];
        foreach ($verseRequiredFields as $field) {
            if (!isset($data['verse'][$field]) || $data['verse'][$field] === '') {
                throw new BadRequestHttpException("Missing required field: verse.{$field}");
            }
        }
        // Fields that must exist as keys but may be null (e.g. empty scenes)
        $verseNullableFields = ['data', 'version'];
        foreach ($verseNullableFields as $field) {
            if (!array_key_exists($field, $data['verse'])) {
                throw new BadRequestHttpException("Missing required field: verse.{$field}");
            }
        }

        // Validate verse.image if present and not null
        if (isset($data['verse']['image']) && $data['verse']['image'] !== null) {
            $imageRequiredFields = ['url', 'filename', 'key'];
            $missingFields = [];
            foreach ($imageRequiredFields as $field) {
                if (!isset($data['verse']['image'][$field]) || $data['verse']['image'][$field] === '') {
                    $missingFields[] = "verse.image.{$field}";
                }
            }
            if (!empty($missingFields)) {
                throw new BadRequestHttpException("Missing required field: " . implode(', ', $missingFields));
            }
        }

        if (isset($data['metas']) && is_array($data['metas'])) {
            $metaRequiredFields = ['title', 'uuid'];
            foreach ($data['metas'] as $index => $meta) {
                foreach ($metaRequiredFields as $field) {
                    if (!isset($meta[$field]) || $meta[$field] === '') {
                        throw new BadRequestHttpException("Missing required field: metas[{$index}].{$field}");
                    }
                }

                // Validate meta.image if present and not null
                if (isset($meta['image']) && $meta['image'] !== null) {
                    $imageRequiredFields = ['url', 'filename', 'key'];
                    $missingFields = [];
                    foreach ($imageRequiredFields as $field) {
                        if (!isset($meta['image'][$field]) || $meta['image'][$field] === '') {
                            $missingFields[] = "metas[{$index}].image.{$field}";
                        }
                    }
                    if (!empty($missingFields)) {
                        throw new BadRequestHttpException("Missing required field: " . implode(', ', $missingFields));
                    }
                }
            }
        }

        if (isset($data['resourceFileMappings']) && is_array($data['resourceFileMappings'])) {
            $mappingRequiredFields = ['originalUuid', 'fileId', 'name', 'type', 'info'];
            foreach ($data['resourceFileMappings'] as $index => $mapping) {
                foreach ($mappingRequiredFields as $field) {
                    if (!isset($mapping[$field]) || $mapping[$field] === '') {
                        throw new BadRequestHttpException("Missing required field: resourceFileMappings[{$index}].{$field}");
                    }
                }

                // Validate resource image if present and not null
                if (isset($mapping['image']) && $mapping['image'] !== null) {
                    $imageRequiredFields = ['url', 'filename', 'key'];
                    $missingFields = [];
                    foreach ($imageRequiredFields as $field) {
                        if (!isset($mapping['image'][$field]) || $mapping['image'][$field] === '') {
                            $missingFields[] = "resourceFileMappings[{$index}].image.{$field}";
                        }
                    }
                    if (!empty($missingFields)) {
                        throw new BadRequestHttpException("Missing required field: " . implode(', ', $missingFields));
                    }
                }
            }
        }
    }

    /**
     * Validate that all fileId references exist.
     *
     * @param array $data
     * @throws UnprocessableEntityHttpException
     */
    private function validateFileIds(array $data): void
    {
        if (!isset($data['resourceFileMappings']) || !is_array($data['resourceFileMappings'])) {
            return;
        }

        foreach ($data['resourceFileMappings'] as $mapping) {
            $fileId = $mapping['fileId'];
            if (File::findOne($fileId) === null) {
                throw new UnprocessableEntityHttpException("File not found for fileId: {$fileId}");
            }
        }
    }
}
