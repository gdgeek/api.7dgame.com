<?php
namespace api\modules\v1\controllers;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\VerseSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use api\modules\v1\models\data\VerseCodeTool;

use yii\base\Exception;
class SnapshotController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Snapshot';
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


    public function actionTakePhoto($id)
    {
        $snapshot = Snapshot::CreateById($id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot;
    }
}
