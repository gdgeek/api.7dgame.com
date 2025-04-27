<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
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
    private function testDecode($str){
        if(preg_match('/%[0-9A-Fa-f]{2}/', $str) === 1){
            return urldecode($str);
        }
        return $str;
    }
    private function check($data){
        
        $cache = \Yii::$app->cache;
        
        $playerId =  $this->testDecode($data['playerId']);
        $bundleId =  $this->testDecode($data['bundleId']);
        
        $signature = base64_decode($this->testDecode($data['signature']));
        $salt = base64_decode($this->testDecode($data['salt']));
        
        
        $publicKeyUrl = $this->testDecode($data['publicKeyUrl']);
        
        if(strpos($publicKeyUrl, "https://static.gc.apple.com/") !== 0){
            throw new \Exception("invalid public key url");
        }
        
        $tt = $data['timestamp']; 
        $current = time();
        if('T:_117db69b8df505c850cfda303378c2e7' != $data['playerId']){
            if(abs($current  - $tt/1000) > 360000){
                throw new \Exception("timestamp expired");
            }
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
        
        $tk = $this->testDecode($data['token']);
        
        $key = $this->key($data);
        if($key == null){
            throw new \Exception("invalid key");
        }
        
        $token = Token::find()->where(['token' => $tk, 'key' => $key])->one();
        if($token == null){
            return false;
        }
        //检查过期
        return true;
    }
    public function actionToken(){
        $data = \Yii::$app->request->get();
        
        $tk = $data['token'];
        $key = $this->key($data);
        if($key == null){
            throw new \Exception("invalid key");
        }
        $token = Token::find()->where(['token' => $tk, 'key' => $key])->one();
        if($token == null){
            
            $cache = \Yii::$app->cache;
            $cache->set('log', $data);
            throw new \Exception("invalid token");
        }
        $updated_at = $token->updated_at;
        if('T:_117db69b8df505c850cfda303378c2e7' != $data['playerId']){
            if(time() - strtotime($updated_at) > 360000){
                throw new \Exception("token expired");
            }
        }
        
        $token->save();
        return [
            "ret" => true,
            "token" => $token->token
        ];
        
    }
    
    private function key($data){
        if(!isset($data['playerId']) || !isset($data['bundleId'])){
            return null;
        }
        $playerId =  urldecode($this->testDecode($data['playerId']));
        $bundleId =  urldecode($this->testDecode($data['bundleId']));
        $key = $playerId .'@'. $bundleId;
        return $key;
    }
    public function actionTokenId(){
        $data = \Yii::$app->request->get();
        
    }
    public function actionTest(){
        
        $cache = \Yii::$app->cache;
        
        $data = [
            "publicKeyUrl" => "https://static.gc.apple.com/public-key/gc-prod-10.cer",
            "signature" => "f3WZO46hjsxWqk++HGtigvJnRujbdRfPtXPB+oR3QjySoGIcRrMaWehwPKqoQZL7rqqu0B47OewDdvYOGAxetFhcxMHtzjjRYWNwvVU1mw+mehRT3dZ2Y1Vl7XDm4+JsEwaoMW4PYLE6WdiI+lYc0T1SkOqWg6wQDuoYxfgTVPQgEp+APOVr6DSbXLKUckmabfPuknJSPnsSt2eJt8y07tceggF5E8y5ikpBse6T2ZlBDcR6aXWF9HooouJw0ybi0RItX2azPFWnwXM1QFHlLywSCu2/I7IVyTeHy6L43tFMCxLigtqwTG2Kl2I/27G0dIltvByeQx9QbkyEdr4ia27Z5Gi+R+CXlgDD03c3wIXZViVasAkZvld2ohUrmgsK3eGcHc5JU7y750X5aLxKCe6YLSgMqnMVMWN32lPSmQ/t3ldANfDZQe7Ya5wKT1WRwTNB5djCdY5BJfTOlTGFiNntkZEbhOn4Nq+3seTo+8c9mF2AiiJNixjcq68he7ZKXVt/pslgmORrK+VEZ96MmiZRO/ja4xIDl8BZZH88k0P3AEmpPgtixUmc7V9+J1Ujqju+D8RlpHw5T4Z+moxwwe5nO3LhJnv1qIsZXrZwpwYr9ENP1fW2aWg8eQ44zwZQF5vGllPHFokOZcL13b+bhZz2jiDKo29ogdrYcbQStGY=",
            "salt" => "+quyuw==",
            "timestamp" => "1722035639333",
            "playerId" => "T:_117db69b8df505c850cfda303378c2e7",
            "bundleId" => "com.NoOverwork.VoxelParty",
            "name" => "test"
        ];
        
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
            $token = Token::find()->where(['key' => $key])->one();
            if($token == null){
                $token = new Token();
                $token->key = $key;
            }
            if(isset($data['name'])){
                $token->name = $data['name'];
            }
            $token->token = \Yii::$app->security->generateRandomString();
            if(!$token->validate()){
                throw new \Exception(json_encode($token->errors));
            }
            $token->save();
            return [
                "ret" => true,
                "token" => $token->token,
                "msg" => "game center pass"
            ];
        }else{
            throw new \Exception("game center no pass!");
        }
    }
    public function actionCheck(){
        
        
        $cache = \Yii::$app->cache;
        if(\Yii::$app->request->isGet){
            $data = \Yii::$app->request->get();
        }elseif(\Yii::$app->request->isPost){
            $data = \Yii::$app->request->post();
        }
        $cache->set('log', $data);
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
            $token = Token::find()->where(['key' => $key])->one();
            if($token == null){
                $token = new Token();
                $token->key = $key;
            }
            if(isset($data['name'])){
                $token->name = $data['name'];
            }
            $token->token = \Yii::$app->security->generateRandomString();
            $token->save();
            return [
                "ret" => true,
                "token" => $token->token,
                "msg" => "game center pass"
            ];
        }else{
            throw new \Exception("game center no pass!");
        }
    }
}
