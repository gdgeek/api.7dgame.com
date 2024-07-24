<?php
namespace api\modules\vp\controllers;

use yii\rest\ActiveController;

class VerificationController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
       
        return $behaviors;
    }
    private function der2pem($der_data)
    {
       $pem = chunk_split(base64_encode($der_data), 64, "\n");
       $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
       return $pem;
    }
    public function actionCheck(){
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
 
        // 获取主机名(包括域名和端口)
        $host = $_SERVER['HTTP_HOST'];
        
        // 获取资源路径
        $uri = $_SERVER['REQUEST_URI'];
        
        // 组合完整的URL
        $url = $protocol . '://' . $host . $uri;
        
        $cache = \Yii::$app->cache;
        $lastUrl = $cache->get('url');
        $cache->set('url', $url);
        return $lastUrl;
        $get = \Yii::$app->request->get();
        $publicKeyUrl = $get['publicKeyUrl'];
        $publicKeyUrl = filter_var($publicKeyUrl, FILTER_SANITIZE_URL);
        $pkURL =  urlencode($publicKeyUrl);
        $signature = base64_decode($get['signature']);
       // $signature = filter_var($signature, FILTER_SANITIZE_STRING);
        $salt = base64_decode($get['salt']);
        //$salt = filter_var($salt, FILTER_SANITIZE_STRING);
        $timestamp = $get['timestamp'];
        //$timestamp = filter_var($timestamp, FILTER_SANITIZE_STRING);

        $playerId = $get['playerId'];
        $bundleId = $get['bundleId'];
        $certificate = file_get_contents ($publicKeyUrl);
        $pemData = $this->der2pem($certificate);
        $ok = openssl_verify($dataToUse, $signatureToUse, $pubkeyid, OPENSSL_ALGO_SHA256);
        $pubkeyid = openssl_pkey_get_public($pemData);
        //$publicKeyURL = filter_var($headers['Publickeyurl'], FILTER_SANITIZE_URL);
           
        /*
            
            $publicKeyURL = filter_var($headers['Publickeyurl'], FILTER_SANITIZE_URL);
            $pkURL = urlencode($publicKeyURL);
        */
        return  $get;
    }
}
