<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\LocalSignupForm;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\Exception;
use yii\rest\ActiveController;

class LocalController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Local';
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
        return $behaviors;
    }
    private function getAdminCount()
    {
        $admin = \common\models\AuthAssignment::findAll(['item_name' => 'admin']);
        return count($admin);
    }
    public function actionReady()
    {

        return [
            'result' => $this->getAdminCount() >= 1,
            'code' => 20000,
        ];

    }
    public function actionInit()
    {
        $ready = $this->getAdminCount() >= 1;
        if (!$ready) {
            $model = new LocalSignupForm();
            $post = Yii::$app->request->post();
            if ($model->load($post, '') && $model->validate()) {
                $model->signup();
                $user = $model->getUser();
                $assignment = new Assignment($user->id);
                $assignment->assign(['admin', 'user']);
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

        } else {
            throw new Exception("重复初始化", 400);
        }

    }
    /*
    public function actionSignup()
    {
    // $local = Local::find()->where(['key' => 'admin'])->one();
    // if ($local == null) {

    $model = new LocalSignupForm();

    //$model->clearAll();
    $post = Yii::$app->request->post();
    if ($model->load($post, '') && $model->validate()) {
    $model->signup();

    $user = $model->getUser();
    $assignment = new Assignment($user->id);
    $assignment->assign(['admin']);

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
    throw new Exception("无法设置", 400);
    //检查是否有admin
    //如果没有，创建root
    //创建并设置admin数据
    }
     */

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
