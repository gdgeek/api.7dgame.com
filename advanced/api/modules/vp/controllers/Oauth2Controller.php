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
    public function actionClear(){
        $cache = \Yii::$app->cache;
        $cache->flush();
        return 'ok';
    }
    private function getRealIpAddr()
    {
        // 处理通过代理的情况
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public function actionAppleIdLogin(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        // 获取 POST 数据
        $code = Yii::$app->request->post('code');
        $idToken = Yii::$app->request->post('id_token');
        $user = Yii::$app->request->post('user');


        // 确保返回 HTTP 200 状态码
        Yii::$app->response->statusCode = 200;
        // 记录收到的数据用于调试
        //file_put_contents('apple_login.log', print_r(Yii::$app->request->post(), true), FILE_APPEND);
        $cache = \Yii::$app->cache;
        $cache->set('apple', Yii::$app->request->post());
        // 返回 HTTP 200 状态码和 JSON 响应
        return ['status' => 'success', 'message' => 'Data received successfully'];
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
