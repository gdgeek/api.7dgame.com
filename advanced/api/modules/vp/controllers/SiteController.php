<?php
namespace api\modules\vp\controllers;

use yii\rest\ActiveController;

class SiteController extends \yii\rest\Controller
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
    public function actionLog(){
        $cache = \Yii::$app->cache;
        $log = $cache->get('log');
        return $log;

    }
    public function actionCheck(){
       
        $cache = \Yii::$app->cache;
  
       
        $data = \Yii::$app->request->get();
        $cache->set('log', json_encode($data));
        try{
            $publicKeyUrl = urldecode($data['publicKeyUrl']);
            $signature = base64_decode(urldecode($data['signature']));
        
        
            $salt = base64_decode(urldecode($data['salt']));
            $timestamp = pack("J",$data['timestamp']);
            $playerId =  urldecode($data['playerId']);
            $bundleId =  urldecode($data['bundleId']);
        

            $dataBuffer = $playerId . $bundleId . $timestamp . $salt;
            
            $pkURL =  urldecode($publicKeyUrl);
            $certificate = file_get_contents ($pkURL);
            $pemData = $this->der2pem($certificate);
            $pubkeyid = openssl_pkey_get_public($pemData);
            
            $ok = openssl_verify(
                        $dataBuffer, 
                        $signature, 
                        $pubkeyid, 
                        OPENSSL_ALGO_SHA256
                    );

            return [
                "ret" => true
            ];
        }catch(\Exception $e){
            return [
                "ret" => false,
                "msg" => $e->getMessage()
            ];
        }
        
    }
}
