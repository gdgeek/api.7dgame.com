<?php
namespace api\modules\v1\controllers;

use common\components\security\RateLimitBehavior;
use common\components\security\UploadValidator;
use common\components\security\ValidationResult;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use OpenApi\Annotations as OA;

/**
 * FileController handles file management and secure file uploads.
 *
 * Integrates UploadValidator for comprehensive file validation including
 * MIME type, extension, file size, double extension, and content scanning.
 *
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.8, 2.9
 *
 * @OA\Tag(
 *     name="File",
 *     description="文件管理接口"
 * )
 */
class FileController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\File';

    /**
     * @var UploadValidator|null Upload validator component instance.
     * Can be injected for testing or will be created automatically.
     */
    private $_uploadValidator;

    public function behaviors()
    {

        $behaviors = parent::behaviors();

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
        $behaviors['rateLimiter'] = [
            'class' => RateLimitBehavior::class,
            'rateLimiter' => 'rateLimiter',
            'defaultStrategy' => 'ip',
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     *
     * Adds the 'upload' action to the default REST actions.
     */
    public function actions()
    {
        return parent::actions();
    }

    /**
     * Get the UploadValidator instance.
     *
     * Attempts to retrieve from Yii application component first,
     * falls back to creating a new instance.
     *
     * @return UploadValidator
     */
    public function getUploadValidator(): UploadValidator
    {
        if ($this->_uploadValidator !== null) {
            return $this->_uploadValidator;
        }

        // Try to get from application component
        if (Yii::$app->has('uploadValidator')) {
            return Yii::$app->get('uploadValidator');
        }

        // Create a new instance as fallback
        return new UploadValidator();
    }

    /**
     * Set the UploadValidator instance (primarily for testing).
     *
     * @param UploadValidator $validator
     */
    public function setUploadValidator(UploadValidator $validator): void
    {
        $this->_uploadValidator = $validator;
    }

    /**
     * @OA\Get(
     *     path="/v1/file",
     *     summary="获取文件列表",
     *     description="获取当前用户的文件列表",
     *     tags={"File"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per-page",
     *         in="query",
     *         description="每页数量",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="文件列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/File")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => false,
        ]);
        return $activeData;
    }

    /**
     * Handle secure file upload with comprehensive validation.
     *
     * Validates the uploaded file using UploadValidator (MIME type, extension,
     * file size, double extension, content scanning) before processing.
     * Logs failed validation attempts for audit purposes (Requirement 2.9).
     *
     * @OA\Post(
     *     path="/v1/file/upload",
     *     summary="上传文件",
     *     description="安全上传文件，包含MIME类型、扩展名、文件大小和内容验证",
     *     tags={"File"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="要上传的文件"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="上传成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="filename", type="string"),
     *                 @OA\Property(property="original_name", type="string"),
     *                 @OA\Property(property="size", type="integer"),
     *                 @OA\Property(property="type", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="验证失败",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="code", type="string", example="UPLOAD_VALIDATION_FAILED"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     *
     * @return array Response data
     */
    public function actionUpload()
    {
        $uploadedFile = UploadedFile::getInstanceByName('file');

        if ($uploadedFile === null) {
            Yii::$app->response->statusCode = 400;
            return $this->formatErrorResponse(
                'UPLOAD_NO_FILE',
                'No file was uploaded.',
                ['No file found in the request. Please upload a file using the "file" field.']
            );
        }

        // Check for PHP upload errors
        if ($uploadedFile->error !== UPLOAD_ERR_OK) {
            $phpError = $this->getPhpUploadErrorMessage($uploadedFile->error);
            $this->logUploadFailure($uploadedFile->name, [$phpError], 'php_upload_error');
            Yii::$app->response->statusCode = 400;
            return $this->formatErrorResponse(
                'UPLOAD_ERROR',
                'File upload failed.',
                [$phpError]
            );
        }

        // Validate the file using UploadValidator
        $validator = $this->getUploadValidator();
        $validationResult = $validator->validate($uploadedFile);

        if (!$validationResult->isValid()) {
            // Requirement 2.9: Log failed upload validation with user information
            $this->logUploadFailure(
                $uploadedFile->name,
                $validationResult->getErrors(),
                'validation_failed'
            );

            Yii::$app->response->statusCode = 400;
            return $this->formatErrorResponse(
                'UPLOAD_VALIDATION_FAILED',
                'File validation failed.',
                $validationResult->getErrors()
            );
        }

        // Generate a safe filename (Requirement 2.5)
        $safeFilename = $validator->generateSafeFilename($uploadedFile->name);
        $storagePath = $validator->getSecureStoragePath();
        $fullPath = $storagePath . DIRECTORY_SEPARATOR . $safeFilename;

        // Ensure the storage directory exists
        if (!is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0750, true)) {
                Yii::warning(
                    'Failed to create upload directory: ' . $storagePath,
                    'security.upload'
                );
                Yii::$app->response->statusCode = 500;
                return $this->formatErrorResponse(
                    'UPLOAD_STORAGE_ERROR',
                    'Failed to store the uploaded file.',
                    ['An internal error occurred while storing the file.']
                );
            }
        }

        // Save the file
        if (!$uploadedFile->saveAs($fullPath)) {
            Yii::warning(
                'Failed to save uploaded file to: ' . $fullPath,
                'security.upload'
            );
            Yii::$app->response->statusCode = 500;
            return $this->formatErrorResponse(
                'UPLOAD_SAVE_ERROR',
                'Failed to save the uploaded file.',
                ['An internal error occurred while saving the file.']
            );
        }

        // Log successful upload
        Yii::info(json_encode([
            'event' => 'file_upload_success',
            'user_id' => Yii::$app->user->id ?? null,
            'original_name' => $uploadedFile->name,
            'safe_name' => $safeFilename,
            'size' => $uploadedFile->size,
            'type' => $uploadedFile->type,
            'ip_address' => Yii::$app->request->userIP ?? null,
        ]), 'security.upload');

        return $this->formatSuccessResponse($uploadedFile, $safeFilename);
    }

    /**
     * Format a structured error response for upload failures.
     *
     * @param string $code Error code identifier
     * @param string $message Human-readable error message
     * @param array $errors Detailed list of validation errors
     * @return array Structured error response
     */
    public function formatErrorResponse(string $code, string $message, array $errors): array
    {
        return [
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * Format a structured success response for completed uploads.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $safeFilename The generated safe filename
     * @return array Structured success response
     */
    public function formatSuccessResponse(UploadedFile $file, string $safeFilename): array
    {
        return [
            'success' => true,
            'data' => [
                'filename' => $safeFilename,
                'original_name' => $file->name,
                'size' => $file->size,
                'type' => $file->type,
            ],
        ];
    }

    /**
     * Log a failed upload validation attempt for audit purposes.
     *
     * Requirement 2.9: When a file upload fails validation, log the attempt
     * with user information and failure reason.
     *
     * @param string $filename Original filename
     * @param array $errors Validation error messages
     * @param string $reason Failure reason category
     */
    protected function logUploadFailure(string $filename, array $errors, string $reason): void
    {
        $logData = [
            'event' => 'file_upload_validation_failed',
            'reason' => $reason,
            'filename' => $filename,
            'errors' => $errors,
            'user_id' => Yii::$app->user->id ?? null,
            'ip_address' => Yii::$app->request->userIP ?? null,
            'user_agent' => Yii::$app->request->userAgent ?? null,
            'timestamp' => date('Y-m-d\TH:i:sP'),
        ];

        Yii::warning(json_encode($logData), 'security.upload');
    }

    /**
     * Get a human-readable message for PHP upload error codes.
     *
     * @param int $errorCode PHP upload error code (UPLOAD_ERR_*)
     * @return string Human-readable error message
     */
    protected function getPhpUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server\'s maximum upload size.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the form\'s maximum upload size.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error: missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload was stopped by a server extension.',
        ];

        return $messages[$errorCode] ?? 'Unknown upload error.';
    }
}
