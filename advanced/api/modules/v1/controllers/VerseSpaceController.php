<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\VerseSpace;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;

class VerseSpaceController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\VerseSpace';

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
        return $behaviors;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => VerseSpace::find()
                ->joinWith('space')
                ->where(['space.user_id' => Yii::$app->user->id])
                ->orderBy(['verse_space.id' => SORT_DESC]),
        ]);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (!$model instanceof VerseSpace) {
            return;
        }

        if (!$model->space || (int) $model->space->user_id !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You do not have permission to access this space binding.');
        }

        if (in_array($action, ['create', 'update'], true) && (!$model->verse || !$model->verse->editable)) {
            throw new ForbiddenHttpException('You do not have permission to bind this verse.');
        }
    }
}
