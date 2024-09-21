<?php

namespace api\modules\v1\controllers;
//namespace api\controllers;

use api\modules\v1\models\data\Login;
use api\modules\v1\models\data\Link;
use api\modules\v1\models\data\Register;
use api\modules\v1\models\User;
use api\modules\v1\models\AppleId;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

class SiteController extends \yii\rest\Controller

{
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
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
    
    
    
    
    private function getAppleUser($code, $appleParameter){
        
        
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple($appleParameter);
        
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        
        $data = $provider->getResourceOwner($token);
        return [
            "id"=>$data->getId(), 
            "email"=>$data->getEmail(), 
            "first_name"=>$data->getFirstName(),
            "last_name"=>$data->getLastName(), 
            "token"=>$token->getToken(),
            "privateEmail" => $data->isPrivateEmail(),
            // "array" => $data->toArray() 
        ];
        
    }
    
    public function actionAppleId(){
        
        
        
        $post = Yii::$app->request->post();
        
        if(!isset($post['data'])){
            throw new \yii\web\NotFoundHttpException('data');
        }
        if(!isset($post['url'])){
            throw new \yii\web\NotFoundHttpException('url');
        }
        if(!isset($post['key'])){
            throw new \yii\web\NotFoundHttpException('key');
        }
        $key = $post['key'];
        $data = $post['data'];
        $url = $post['url'];
        
        if(!isset($data['userData']) || !isset($data['userData']['aud'])){
            throw new \yii\web\NotFoundHttpException('userData.aud');
        }
        $aud = $data['userData']['aud'];
        
        if(!isset($data['authorization'])){
            throw new \yii\web\NotFoundHttpException('authorization');
        }
        if(!isset($data['authorization']['code'])){
            throw new \yii\web\NotFoundHttpException('code');
        }
        $appleParameter = [
            'clientId'          =>  $aud, // com.mrpp.www //客户端提供
            'teamId'            =>  getenv('APPLE_TEAM_ID') , // PK435YWZ25 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         =>  getenv($key) , // UZJ8VJVX7K https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       =>  str_replace('{KEY_ID}', getenv($key), getenv('APPLE_AUTH_KEY')), // __DIR__ . '/AuthKey_UZJ8VJVX7K.p8' -> Download key above
            'redirectUri'       =>  $url,
            'scope'             => "email name",
        ];
        
        
        $code = $data['authorization']['code'];
        $result = $this->getAppleUser($code, $appleParameter);
        $aid = AppleId::find()->where(['apple_id'=>$result['id']])->one();
        if($aid && $aid->user){
            return $aid;
        }
        
        if(!$aid){
            $aid = new AppleId();
            $aid->apple_id = $result['id'];
            $aid->email = $result['email'];
            $aid->first_name = $result['first_name'];
            $aid->last_name = $result['last_name'];
            $user = User::find()->where(['email'=>$result['email']])->one();
            if($user){
                $aid->user_id = $user->id;
                if($aid->validate()){
                    $user->addRoles(['mrpp.com']);
                    $aid->save();
                }
            }else{
                $aid->token = $result['token'];
                if($aid->validate()){
                    $aid->save();
                }else{
                    throw new Exception(json_encode($aid->errors));
                }
            }
            return $aid;
        }else{
            $aid->token = $result['token'];
            if($aid->validate()){
                $aid->save();
            }else{
                throw new Exception(json_encode($aid->errors));
            }
            return $aid;
        }
        
    }
    public function actionTest(){
        Yii::$app->redis->set('key', 'value');
        $value = Yii::$app->redis->get('key');
        return $value;
    }
    
    public function actionAppleIdCreate()
    {
        $register = new Register();
        $appleId = Yii::$app->request->post('apple_id');
        if (!$appleId) {
            throw new Exception(json_encode(Yii::$app->request->post()), 400);
        }
        
        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }
        $aid = AppleId::find()->where([ 'apple_id'=>$appleId])->one();
        if(!$aid){
            throw new Exception("No Apple Id", 400);
        }
        if($aid->user !== null)
        {
            
            $aid->token = null;
            if($aid->validate()){
                $aid->save();
            }
            return $aid;
        }
        
        if ($register->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            
            $nickname = null;
            if($aid->first_name){
                $nickname = $aid->first_name;
            }
            if($nickname && $aid->last_name){
                $nickname += ' ';
            } 
            if($aid->last_name){
                $nickname += $aid->last_name;
            }
            if ($register->create($aid->email, $nickname)) {
                
                $aid->user_id = $register->user->id;
                
                $aid->token = null;
                if($aid->validate()){
                    $register->user->addRoles(['mrpp.com']);
                    $aid->save();
                }else{
                    $register->remove();
                    throw new Exception(json_encode($aid->errors), 400);
                }
                return $aid;
            } else {
                throw new Exception("error!", 400);
            }
        } else {
            throw new Exception(json_encode($register->getFirstErrors()), 400);
        }
        
    }
    public function actionAppleIdLink(){
        $link = new Link();
        $appleId = Yii::$app->request->post('apple_id');
        if (!$appleId) {
            throw new Exception(("No AppleId"), 400);
        }
        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }
        
        $aid = AppleId::find()->where(['apple_id'=>$appleId, 'token'=> $token])->one();
        if(!$aid){
            throw new Exception("No AppleId", 400);
        }
        if($aid->user !== null)
        {
            $aid->token = null;
            $aid->save();
            return $aid;
        }
        if ($link->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            
            if ($link->bind()) {
                $aid->user_id = $link->user->id;
                
                $aid->token = null;
                if($aid->validate()){
                    $link->user->addRoles(['mrpp.com']);
                    $aid->save();
                }else{
                    throw new Exception(json_encode($aid->errors), 400);
                }
                return $aid;
            } else {
                throw new Exception('Error', 400);
            }
        } else {
            throw new Exception(json_encode($model->getFirstErrors()), 400);
        }
    }
    /**
    * @return array
    * @throws \yii\base\Exception
    * @throws \yii\base\InvalidConfigException
    */
    public function actionLogin()
    {
        $login = new Login();
        if ($login->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            if ($login->login()) {
                return $login->user->toArray([],['auth']);
            } else {
                throw new Exception("Login Error", 400);
            }
        } else {
            throw new Exception(json_encode($login->getFirstErrors()), 400);
        }
    }
    
}
