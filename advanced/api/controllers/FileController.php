<?php
namespace api\controllers;

use common\components\security\UploadValidator;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use yii\data\ActiveDataProvider;

use yii\filters\auth\CompositeAuth;

use bizley\jwt\JwtHttpBearerAuth;

use common\models\Project;
use common\models\Programme;
use yii\web\UploadedFile;

/**
 * Legacy FileController.
 *
 * Note: The primary file upload endpoint is in api\modules\v1\controllers\FileController.
 * This controller is maintained for backward compatibility.
 */
class FileController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\File';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
    
        return $behaviors;
    }

    public function actionIndex() {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' =>  Yii::$app->user->id]),
            'pagination' => false
        ]);
        return $activeData;
    }

    /**
     * Handle file upload with security validation.
     *
     * Delegates to the UploadValidator for comprehensive file validation
     * including MIME type, extension, file size, double extension, and content scanning.
     *
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.8, 2.9
     *
     * @return array Response data
     */
    public function actionUpload()
    {
        $uploadedFile = UploadedFile::getInstanceByName('file');

        if ($uploadedFile === null) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'code' => 'UPLOAD_NO_FILE',
                'message' => 'No file was uploaded.',
                'errors' => ['No file found in the request. Please upload a file using the "file" field.'],
            ];
        }

        // Check for PHP upload errors
        if ($uploadedFile->error !== UPLOAD_ERR_OK) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'code' => 'UPLOAD_ERROR',
                'message' => 'File upload failed.',
                'errors' => ['A PHP upload error occurred.'],
            ];
        }

        // Validate using UploadValidator
        $validator = Yii::$app->has('uploadValidator')
            ? Yii::$app->get('uploadValidator')
            : new UploadValidator();

        $result = $validator->validate($uploadedFile);

        if (!$result->isValid()) {
            // Requirement 2.9: Log failed upload validation
            Yii::warning(json_encode([
                'event' => 'file_upload_validation_failed',
                'filename' => $uploadedFile->name,
                'errors' => $result->getErrors(),
                'user_id' => Yii::$app->user->id ?? null,
                'ip_address' => Yii::$app->request->userIP ?? null,
                'timestamp' => date('Y-m-d\TH:i:sP'),
            ]), 'security.upload');

            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'code' => 'UPLOAD_VALIDATION_FAILED',
                'message' => 'File validation failed.',
                'errors' => $result->getErrors(),
            ];
        }

        // Generate safe filename and save
        $safeFilename = $validator->generateSafeFilename($uploadedFile->name);
        $storagePath = $validator->getSecureStoragePath();
        $fullPath = $storagePath . DIRECTORY_SEPARATOR . $safeFilename;

        if (!is_dir($storagePath)) {
            @mkdir($storagePath, 0750, true);
        }

        if (!$uploadedFile->saveAs($fullPath)) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'code' => 'UPLOAD_SAVE_ERROR',
                'message' => 'Failed to save the uploaded file.',
                'errors' => ['An internal error occurred while saving the file.'],
            ];
        }

        Yii::info(json_encode([
            'event' => 'file_upload_success',
            'user_id' => Yii::$app->user->id ?? null,
            'original_name' => $uploadedFile->name,
            'safe_name' => $safeFilename,
            'size' => $uploadedFile->size,
            'type' => $uploadedFile->type,
        ]), 'security.upload');

        return [
            'success' => true,
            'data' => [
                'filename' => $safeFilename,
                'original_name' => $uploadedFile->name,
                'size' => $uploadedFile->size,
                'type' => $uploadedFile->type,
            ],
        ];
    }
}
