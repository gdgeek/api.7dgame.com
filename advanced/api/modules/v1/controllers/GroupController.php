<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Group;
use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;

class GroupController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Group';

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
     * Join a group
     * @return \api\modules\v1\models\GroupUser
     */
    public function actionJoin()
    {
        $userId = Yii::$app->user->id;
        $groupId = Yii::$app->request->post('group_id');
        
        if (!$groupId) {
            throw new \yii\web\BadRequestHttpException('Group ID is required.');
        }
        
        $group = Group::findOne($groupId);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }
        
        $model = \api\modules\v1\models\GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $groupId])
            ->one();
            
        if (!$model) {
            $model = new \api\modules\v1\models\GroupUser();
            $model->user_id = $userId;
            $model->group_id = $groupId;
            if (!$model->save()) {
                throw new \yii\web\ServerErrorHttpException('Failed to join group.');
            }
        }
        
        return $model;
    }
}
