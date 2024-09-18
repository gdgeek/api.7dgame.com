<?php

namespace api\modules\v1\controllers;
//namespace api\controllers;

use api\modules\v1\models\Login;
use api\modules\v1\models\User;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

class SiteController extends \yii\rest\Controller

{
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        
        return $behaviors;
    }
    private function der2pem($der_data)
    {
        $pem = chunk_split(base64_encode($der_data), 64, "\n");
        $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
        return $pem;
    }
    /**
    * @return array
    * @throws \yii\base\Exception
    * @throws \yii\base\InvalidConfigException
    */
    public function actionLogin()
    {
        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $token = $model->login();
            
            
            if ($token) {
                $user = $model->user->getUser();
                return [
                    'access_token' => $token,
                    'user' => $user,
                    //'code' => 20000,
                ];
            } else {
                throw new Exception(json_encode("用户名或密码错误"), 400);
            }
        } else {
            throw new Exception(json_encode($model->getFirstErrors()), 400);
        }
    }
    
}
