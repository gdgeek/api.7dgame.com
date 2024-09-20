<?php

namespace api\modules\v1\controllers;
//namespace api\controllers;

use api\modules\v1\models\data\Login;
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
        $user = $this->getAppleUser($code, $appleParameter);
        $aid = AppleId::find()->where(['apple_id'=>$user['id']])->one();
        if(!$aid){
            $aid = new AppleId();
            $aid->apple_id = $user['id'];
            $aid->email = $user['email'];
            $aid->first_name = $user['first_name'];
            $aid->last_name = $user['last_name'];
        }
        
        $aid->token = $user['token'];
        if($aid->validate()){
            $aid->save();
        }else{
            throw new Exception(json_encode($aid->errors));
        }
        
        return $aid;
    }
    
    public function actionAppleIdCreate()
    {
        $register = new Register();
        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }
        
        $aid = AppleId::find()->where(['token'=> $token])->one();
        if(!$aid){
            throw new Exception("No AppleId", 400);
        }
        if($aid->user !== null)
        {
            throw new Exception("Already Registered", 400);
        }
        
        if ($register->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            
            if ($register->validate()) {
                $register->user->save();
                $aid->user_id = $register->user->id;
                $aid->token = null;
                if($aid->validate()){
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
    public function actionAppleIdLink(){
        $link = new Link();
        $token = Yii::$app->request->post('token');
        if (!$token) {
            throw new Exception(("No Token"), 400);
        }
        
        $aid = AppleId::find()->where(['token'=> $token])->one();
        if(!$aid){
            throw new Exception("No AppleId", 400);
        }
        if($aid->user !== null)
        {
            throw new Exception("Already Registered", 400);
        }
        if ($link->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            
            if ($link->validate()) {
                $aid->user_id = $link->user->id;
                $aid->token = null;
                if($aid->validate()){
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
