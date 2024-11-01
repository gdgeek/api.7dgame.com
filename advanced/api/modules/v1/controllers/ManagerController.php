<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Local;
use api\modules\v1\models\LocalSignupForm;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\Exception;
use yii\rest\ActiveController;

class ManagerController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\User';
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

    public function actionReady()
    {
        $local = Local::find()->where(['key' => 'admin'])->one();
        return ['result' => ($local != null)];
    }
    public function actionSignup()
    {
        $local = Local::find()->where(['key' => 'admin'])->one();
        if ($local == null) {

            $model = new LocalSignupForm();

            $model->clearAll();
            $post = Yii::$app->request->post();
            if ($model->load($post, '') && $model->validate()) {
                $model->signup();

                $user = $model->getUser();
                $assignment = new Assignment($user->id);
                $assignment->assign(['root', 'user']);

                $access_token = $user->generateAccessToken();
                $local = new Local();
                $local->key = 'admin';
                $local->value = json_encode(['username' => $user->username]);
                $local->save();
//更新 local
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
        throw new Exception("无法设置", 400);
        //检查是否有admin
        //如果没有，创建root
        //创建并设置admin数据
    }

    public function actionParam()
    {
        return [
            'title' => Yii::$app->params['information']['title'],
            'description' => Yii::$app->params['information']['sub-title'],
            'company' => Yii::$app->params['information']['company'],
        ];
    }
    public function actionInformation()
    {

        return [
            'title' => Yii::$app->params['information']['title'],
            'description' => Yii::$app->params['information']['sub-title'],
            'companies' => [
                ['name' => Yii::$app->params['information']['company'], 'url' => Yii::$app->params['information']['company-url']],
            ],
            'version' => null,
            'beian' => null,
            'logo' => '/local/logo.jpeg',
        ];

    }

}
