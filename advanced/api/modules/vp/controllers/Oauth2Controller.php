<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
use yii\rest\ActiveController;
use Yii;

use yii\web\Response;
class Oauth2Controller extends \yii\rest\Controller{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
       
        return $behaviors;
    }
    
    public function actionAppleIdLogin(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('code');
            $idToken = Yii::$app->request->post('id_token');
            $user = Yii::$app->request->post('user');

            // 记录收到的数据，便于调试
          //  Yii::info(Yii::$app->request->post(), 'apple_login');

            $cache = \Yii::$app->cache;
            $cache->set('apple', Yii::$app->request->post());
            $cache->set('apple_code', "$code");
            // 返回 HTTP 200 状态码
            Yii::$app->response->statusCode = 200;
            return ['status' => 'success', 'message' => 'Data received successfully'];
        }

        // 处理非 POST 请求
        Yii::$app->response->statusCode = 405;
        return ['status' => 'error', 'message' => 'Method Not Allowed'];
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
