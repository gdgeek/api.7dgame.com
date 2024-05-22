<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\PersonRegister;
use mdm\admin\components\AccessControl;
use mdm\admin\models\Assignment;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class PersonController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Person';
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
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['options']);
        return $actions;
    }
    public function actionCreate()
    {
        $model = new PersonRegister();

        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->validate()) {
            $model->signup();
            $user = $model->getUser();
            $assignment = new Assignment($user->id);
            $assignment->assign(['user']);

            $access_token = $user->generateAccessToken();

            return [
                'access_token' => $access_token,
                'code' => 20000,
            ];
        } else {

            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }
        }

       

    }

    public function actionAuthority($authority)
    { 
        $model = new Assignment($this->id);
      
        switch ($authority) {
            case 'manager':
                $items = ['manager'];
                $success = $model->assign($items);
                break;
            case 'admin':
                $items = ['admin'];
                $success = $model->assign($items);
                break;
            case 'user':
                $items = ['admin'];
                $success = $model->assign($items);
                break;
            default:
                throw new Exception("无效的权限", 400);
                break;
        }
        return array_merge($model->getItems(), ['success' => $success]);
      

    }
}
