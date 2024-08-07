<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
use yii\rest\ActiveController;
use Yii;
use bizley\jwt\JwtTools;
use yii\helpers\Url;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;

use yii\web\Response;
class Oauth2Controller extends \yii\rest\Controller{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];  
        return $behaviors;
    }
    public function actionTest(){
       
        $token = 123;
        $user = 123;
        $url = Url::to( getenv('APPLE_LOGIN_FRONTEND') .'?'.http_build_query(['token'=>$token,'apple_id'=>$user,'register'=>0]));
        return $this->redirect( $url, 302);
        
    }
    public function actionClear(){
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
        $cache = \Yii::$app->cache;
        $cache->set('apple', ['ip'=>$this->getRealIpAddr(),'get'=>Yii::$app->request->get(),'post'=>Yii::$app->request->post()]);

        $session = Yii::$app->session;

        // 检查会话是否已经启动
        if (!$session->isActive) {
            $session->open();
        }


        $post = Yii::$app->request->post();

       //$redirectUri = Yii::$app->request->getHostInfo() . '/' . Yii::$app->request->getPathInfo();
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => getenv('APPLE_CLIENT_ID'), // com.voxelparty.www
            'teamId'            => getenv('APPLE_AUTH_TEAM_ID') , // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => getenv('APPLE_AUTH_KEY_ID') , // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => getenv('APPLE_REDIRECT_URI'),
            'scope'             => "email name",
        ]);
        if(isset($post['id_token'])){
            $jwt = $post['id_token'];
            $tools = new JwtTools();
            $token = $tools->parse($jwt);
            $all = $token->claims()->all();
        }
        if(isset($post['code'])){
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $post['code']
            ]);
            $user = $provider->getResourceOwner($token);
            
            $aid = AppleId::find()->where(['apple_id'=>$user->getId()])->one();
            if($aid == null){
                $aid = new AppleId();
                $aid->apple_id = $user->getId();
                $aid->email = $user->getEmail();
                $aid->first_name = $user->getFirstName();
                $aid->last_name = $user->getLastName();
                $aid->token = $token->getToken();
                $aid->save();
            }

            
            $url = Url::to( getenv('APPLE_LOGIN_FRONTEND') .'?'.http_build_query(['token'=>$token->getToken(),'apple_id'=>$user->getId(),'register'=>($aid->user_id == null)?1:0]));
            return $this->redirect( $url, 302);
        }
       
        throw new \yii\web\NotFoundHttpException('Not Found');
       
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
