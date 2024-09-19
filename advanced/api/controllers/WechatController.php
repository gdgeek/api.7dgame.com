<?php

namespace api\controllers;

use api\modules\v1\models\data\Login;
use api\modules\v1\models\User;
use common\models\WechatSignupForm;
use common\models\Wx;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\Exception;

class WechatController extends \yii\rest\Controller

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
    
    private function getWx($token): ?Wx
    {
        $wx = Wx::find()->where(['token' => $token])->one();
        return $wx;
        
    }
    
    private function binding($user, $wx)
    {
        $user->wx_openid = $wx->wx_openid;
        
        if ($user->validate()) {
            $user->save();
            $wx->delete();
        } else {
            throw new Exception(json_encode($user->getFirstErrors()), 400);
        }
    }
    /*
    public function actionTest()
    {
    
    $wechat = \Yii::$app->wechat;
    $app = $wechat->application();
    $openid = 'oHTTl6NsYDVCHy0tNMhOb5SiuuNE';
    $api = $app->getClient();
    $response = $api->get("/cgi-bin/user/info?openid=ol2S458vttGUvay69b2nK2arftek");
    throw new Exception(json_encode($response->toArray()), 400);
    }*/
    public function actionBinding()
    {
        
        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $access_token = $model->login();
            if ($access_token) {
                
                $post = Yii::$app->request->post();
                $token = $post['token'];
                
                $wx = $this->getWx($token);
                $this->binding($model->getUser(), $wx);
                return [
                    'access_token' => $access_token,
                    'code' => 20000,
                ];
            } else {
                throw new Exception(json_encode("用户名或密码错误"), 400);
            }
        } else {
            throw new Exception(json_encode($model->getFirstErrors()), 400);
        }
    }
    public function actionSignup()
    {
        
        $post = Yii::$app->request->post();
        $token = $post['token'];
        $wx = $this->getWx($token);
        if ($wx == null) {
            throw new Exception(json_encode("无法找到微信数据"), 400);
        }
        
        $model = new WechatSignupForm();
        $parameters = [];
        $parameters['username'] = $post['username'];
        $parameters['password'] = $post['password'];
        $parameters['wx_openid'] = $wx->wx_openid;
        $parameters['nickname'] = $post['username'];
        $model->load($parameters, '');
        if ($model->validate()) {
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
    
    public function actionOpenid($token)
    {
        $wx = $this->getWx($token);
        if (isset($wx)) {
            
            $ret['token'] = $wx->token;
            $user = $wx->user;
            
            if (isset($user)) {
                $ret['user'] = $user->getUser();
                $ret['access_token'] = $user->generateAccessToken();
                $wx->delete();
            }
            return $ret;
        }
        return new \stdClass();
        
    }
    public function actionQrcode()
    {
        $wechat = \Yii::$app->wechat;
        $app = $wechat->application();
        
        $api = $app->getClient();
        $lifetime = 3600;
        $token = Yii::$app->security->generateRandomString();
        $json =
        [
            "expire_seconds" => $lifetime,
            "action_name" => "QR_STR_SCENE",
            "action_info" =>
            [
                "scene" => ["scene_str" => $token],
            ],
        ];
        
        $response = $api->post('/cgi-bin/qrcode/create', ['body' => json_encode($json, JSON_UNESCAPED_UNICODE)]);
        
        $result = $response->toArray();
        return ['qrcode' => $result['ticket'], 'token' => $token, 'lifetime' => $lifetime];
    }
    
}
