<?php
namespace api\modules\p1\controllers;

use api\modules\v1\models\Login;
use api\modules\v1\models\User;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\web\BadRequestHttpException;

class SiteController extends \yii\rest\Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //$auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'], 'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
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

        if ($this->action->id != 'login') {
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
        }

        return $behaviors;
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {

        $model = new Login();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $token = $model->login();
            $user = $model->user;

            if ($token) {
                return [
                    'access_token' => $token,
                    'user' => $user,
                ];
            } else {

                throw new BadRequestHttpException("用户名或密码错误");

            }
        } else {
            throw new Exception(json_encode($model->getErrors()), 400);
        }
    }
    public function actionUser()
    {
        $user = new \stdClass();
        $user->username = Yii::$app->user->identity->username;
        $user->data = Yii::$app->user->identity->getData();
        return $user;
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();
        $ret = new \stdClass();
        return $ret;
    }

}
