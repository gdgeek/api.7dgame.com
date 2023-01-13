<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Like;
use api\modules\v1\models\LikeSearch;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class LikeController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Like';
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

    public function actions()
    {
        $actions = parent::actions();
        //  unset($actions['delete']);
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new LikeSearch();
        $query = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($query);
        return $dataProvider;
    }
    public function actionRemove($message_id = null)
    {
        $model = Like::findOne(['message_id' => $message_id, 'user_id' => Yii::$app->user->identity->id]);
        if ($model == null) {
            throw new BadRequestHttpException('无效 message id');
        }
        $model->delete();
        return Yii::$app->user->identity->id;
    }

}
