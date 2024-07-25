<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\VpToken;
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
    private function check($data){

        $cache = \Yii::$app->cache;
        
        $playerId =  urldecode($data['playerId']);
        $bundleId =  urldecode($data['bundleId']);

        $signature = base64_decode(urldecode($data['signature']));
        $salt = base64_decode(urldecode($data['salt']));
      
       
        $publicKeyUrl = urldecode($data['publicKeyUrl']);
        if(strpos($publicKeyUrl, "https://static.gc.apple.com/") !== 0){
            throw new \Exception("invalid public key url");
        }

        $tt = $data['timestamp']; 
        $current = time();
        if(abs($current * 1000 - $tt) > 3000000000){
            throw new \Exception("timestamp expired");
        }
        $timestamp = pack("J", $tt);
      
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
        return $ok;
    }
    public function checkToken($data){
        if(!isset($data['token'])){
            return false;
        }
        if(!isset($data['playerId'])){
            return false;
        }
        if(!isset($data['bundleId'])){
            return false;
        }

        $tk = $data['token'];
      
        $key = $this->key($data);
        if($key == null){
            throw new \Exception("invalid key");
        }
        
        $token = VpToken::find()->where(['token' => $tk, 'key' => $key])->one();
        if($token == null){
            return false;
        }
        //检查过期
        return true;
    }
    public function actionToken(){
        $data = \Yii::$app->request->get();
        
        try{
            $tk = $data['token'];
            $key = $this->key($data);
            if($key == null){
                throw new \Exception("invalid key");
            }
            $token = VpToken::find()->where(['token' => $tk, 'key' => $key])->one();
            if($token == null){
               throw new \Exception("invalid token");
            }
            $updated_at = $token->updated_at;
            if(time() - strtotime($updated_at) > 360000){
                throw new \Exception("token expired");
            }
           // $token->token = \Yii::$app->security->generateRandomString();
            $token->save();
            return [
                "ret" => true,
                "token" => $token->token
            ];
        }catch(\Exception $e){
            return [
                "ret" => false,
                "msg" => $e->getMessage()
            ];
        }
    }
    private function key($data){
        if(!isset($data['playerId']) || !isset($data['bundleId'])){
          return null;
        }
        $playerId =  urldecode($data['playerId']);
        $bundleId =  urldecode($data['bundleId']);
        $key = $playerId .'@'. $bundleId;
        return $key;
    }
    public function actionTest(){
        
        $data = [
            "publicKeyUrl" => "https%3a%2f%2fstatic.gc.apple.com%2fpublic-key%2fgc-prod-10.cer",
            "signature" => "ZGSMDFVRCZsZdrwK9lBfjypo3sSrateCIETsZFSmLI4W6vqappEcy5sCzTnBf1zyEkok8cHzhhzRbj%2bC6AbI9mayu7BefU1bmlVpcLpHOE53Kk7pStaTSAsCorSPPdvy1BkgGkoyd9gu4ALwHvf%2bJoT6aGhySHqSj6Ao1qi%2fxar3Ur32LKNq1FaDbCBEf%2f7Zgx2uwqGWQi%2bkJPhlQierNFm0d1uiquPRYYR2rhrOFjU0QULCWvXETODbkKyUsXmnooSd%2bkeLiqL%2b32gjoEP8U%2bYb%2bdakjEBZaONfYDzmL8d%2biBvI90suDKBalax6IBPtItSgKOMU5RfxKmrqO0zZ0V9E4A8zisjk7TlrA3NKBL5C2KXMuWh0CMUqYGAOEy2SXuDSSx6%2fCXtPWRtPD9yXKml%2fzU7pN1EMyRsmJFL0E58TtxabdhgD%2bKG%2bDrSVQbPr%2blBIjiQVgnBuZti7DvG1cUlBAPLqM96Nikt7ZEyPVSZR0Hje%2f2f6wUv0exGIxqU19CaiIciHMuMRAwDzCwq4AaQGtvWMjGH2ZdB2OU%2fdyF1ZqYcr%2f87v7odO6eLNDxyXtQeNiDJB6gl9rb8oKyDNnEU5%2fTNm0Igv%2fgsmzVuxOC6kwzhLs0XCGI%2fWvIjLJum%2bD%2fuFbk6Vd7mNCjegM6u5KWW%2fwd%2b5AUoRvMzRnlBlKww%3d",
            "salt" => "llLBIA%3d%3d",
            "timestamp" => "1721799800593",
            "playerId" => "T%3a_117db69b8df505c850cfda303378c2e7",
            "bundleId" => "com.NoOverwork.VoxelParty"
        ];
        try{
            $pass = $this->checkToken($data);
            if($pass){
                return [
                    "ret" => true,
                    "token" => $data['token'],
                    "msg" => "token pass"
                ];
            }
            $ok = $this->check($data);//game center chenk
            $ret = ($ok != 0);
            if($ret){//构建token
                $key = $this->key($data);
                if($key == null){
                    throw new \Exception("invalid key");
                }
                $token = VpToken::find()->where(['key' => $key])->one();
                if($token == null){
                    $token = new VpToken();
                    $token->key = $key;
                }
                $token->token = \Yii::$app->security->generateRandomString();
                $token->save();
                return [
                    "ret" => true,
                    "token" => $token->token,
                    "msg" => "game center pass"
                ];
             }else{
                return [
                    "ret" => false,
                    "msg" => "game center no pass!"
                 ];
             }
            
         }catch(\Exception $e){
             return [
                 "ret" => false,
                 "msg" => $e->getMessage()
             ];
         }
    }
    public function actionCheck(){
       
        $cache = \Yii::$app->cache;
        $data = \Yii::$app->request->get();
        try{
            $pass = $this->checkToken($data);
            if($pass){
                return [
                    "ret" => true,
                    "token" => $data['token'],
                    "msg" => "token pass"
                ];
            }
            $ok = $this->check($data);//game center chenk
            $ret = ($ok != 0);
            if($ret){//构建token
                $key = $this->key($data);
                if($key == null){
                    throw new \Exception("invalid key");
                }
                $token = VpToken::find()->where(['key' => $key])->one();
                if($token == null){
                    $token = new VpToken();
                    $token->key = $key;
                }
                $token->token = \Yii::$app->security->generateRandomString();
                $token->save();
                return [
                    "ret" => true,
                    "token" => $token->token,
                    "msg" => "game center pass"
                ];
             }else{
                return [
                    "ret" => false,
                    "msg" => "game center no pass!"
                 ];
             }
            
         }catch(\Exception $e){
             return [
                 "ret" => false,
                 "msg" => $e->getMessage()
             ];
         }
        
    }
}
