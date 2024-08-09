<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
use yii\rest\ActiveController;
use Yii;
use yii\base\Exception;
use bizley\jwt\JwtTools;
use yii\helpers\Url;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use api\modules\vp\models\AppleId;
use api\modules\vp\models\User;
use mdm\admin\models\Assignment;
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
    
    public function actionRegister(){
        $post = Yii::$app->request->post();
        if(!isset($post['username']) || !isset($post['password']) || !isset($post['token']) || !isset($post['apple_id'])){
            throw new Exception('Params Error');
        }
        $username = $post['username'];
        $password = $post['password'];
        $token = $post['token'];
        $apple_id = $post['apple_id'];

        

        $apple = AppleId::find()->where(['apple_id'=>$apple_id, "token"=>$token])->one();
        if($apple === null){
            throw new Exception('apple_id Not Found');
        }
        if($apple->user_id !== null){
            //已经有用户绑定，返回Token
            return [
                'type' => 'do nothing',
                'token' => $apple->user->generateAccessToken()
            ];
        }
        $user = User::find()->where(['username'=>$username])->one();
        if($user !== null){
            if($user->appleId !== null){
                throw new Exception('Username Already Bind');
            }
            if($password !== null && $user->validatePassword($password)){
                $apple->user_id = $user->id;
               // $apple->token = null;//清除token
                $apple->save();
                //成功绑定旧的用户
                return [
                    'type' => 'binded',
                    'token' => $user->generateAccessToken()
                ];
            }else{
                throw new Exception('Password Error');
            }
        }

        //创建User
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)){
            throw new Exception('Password Not Safe');
        }
        //检查密码最低安全 8 位，数字，大小写字母
        $user = User::create($username, $password);
        $user->email = $apple->email;
        $user->nickname = $apple->first_name . ' ' . $apple->last_name;
        if(!$user->validate()){
            throw new Exception(json_encode($user->errors));
        }
        $user->save();
        
        //分配角色user
        $assignment = new Assignment($user->id);
        $assignment->assign(['user']);
        $access_token = $user->generateAccessToken();

        //绑定apple_id
        $apple->user_id = $user->id;
        $apple->token = null;//清除token
        $apple->save();
        return [
            'type' => 'created',
            'token' => $access_token,
        ];

        //检查apple_id 和 token
        //如果失败返回，如果已经绑定，返回

        //检查是否有User
        //如果有，检查密码是否对
        //如果密码对，绑定apple_id，返回Token
        //如果密码不对，提示用户名已被占用

        //如果没有，创建User，绑定apple_id
        //创建Token并返还

    }
    public function actionTest(){
        $cache = \Yii::$app->cache;
   //     $cache->set('apple', ['ip'=>$this->getRealIpAddr(),'get'=>Yii::$app->request->get(),'post'=>Yii::$app->request->post(),'all'=>$all]);
 return $cache->get('apple');
         
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
    

    public function actionLogin(){
       
        $post = Yii::$app->request->post();
        if(!isset($post['apple_id']) || !isset($post['token'])){
            throw new Exception('Params Error');
        }
        $apple_id = $post['apple_id'];
        $token = $post['token'];
        if($token == null){
            throw new Exception('Token Error');
        }

      
        $apple = AppleId::find()->where(['apple_id'=>$apple_id, "token"=>$token])->one();
        $this->login($apple);
       
    }
    public function actionAppleId(){
        
        $post = Yii::$app->request->post();
        
        \Firebase\JWT\JWT::$leeway = 60;
        $url = getenv('APPLE_REDIRECT_URI');
        if(isset($post['url'])){
            $url = $post['url'];
        }
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => getenv('APPLE_CLIENT_ID'), // com.voxelparty.www
            'teamId'            => getenv('APPLE_AUTH_TEAM_ID') , // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => getenv('APPLE_AUTH_KEY_ID') , // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => $url,
            'scope'             => "email name",
        ]);
        if(isset($post['id_token'])){
            $jwt = $post['id_token'];
            $tools = new JwtTools();
            $token = $tools->parse($jwt);
            $all = $token->claims()->all();
        }else{
            throw new Exception('Not Found id_token');
        }
        $cache = \Yii::$app->cache;
        $cache->set('apple', ['ip'=>$this->getRealIpAddr(),'get'=>Yii::$app->request->get(),'post'=>Yii::$app->request->post(),'all'=>$all]);

        if(isset($post['code'])){
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $post['code']
            ]);
            $user = $provider->getResourceOwner($token);
            
            $apple = AppleId::find()->where(['apple_id'=>$user->getId()])->one();
            if($apple == null){
                $apple = new AppleId();
                $apple->apple_id = $user->getId();
                $apple->email = $user->getEmail();
                $apple->first_name = $user->getFirstName();
                $apple->last_name = $user->getLastName();
                $apple->token = $token->getToken();
                
            }
            if(isset($post['state']) && $apple->vp_token_id == null){
                $state = $post['state'];
                $pattern = '/^T0(\d+)$/';
                $matches = [];
                if (preg_match($pattern, $input, $matches)) {
                   
                    $number = $matches[1];
                    $vpToken = Token::find()->where(['id'=>$number])->one();
                    if($vpToken !== null){
                        $apple->vp_token_id = $vpToken->id;
                    }
                }
            }
            $apple->save();
            return $this->login($apple);
        }else{
            throw new \yii\web\NotFoundHttpException('Not Found Code');
        }
       
    }
    private function login($apple){
        if($apple === null){
            throw new Exception('apple_id Not Found');
        }
        if($apple->user_id !== null){
           // $apple->token = null;//清除token
            $apple->save();
            //已经有用户绑定，返回Token
            return [
                'type' => 'login',
                'token' => $apple->user->generateAccessToken()
            ];
        }else{
            if($apple->email !== null){
                $user = User::find()->where(['email'=>$apple->email])->one();
                if($user !== null){
                    $apple->user_id = $user->id;
                    $apple->save();
                    return [
                        'type' => 'email',
                        'token' => $user->generateAccessToken()
                    ];
                }
            }
            $user = new User();
            $user->username = $apple->apple_id;
            $user->email = $apple->email;
            $user->nickname = $apple->first_name . ' ' . $apple->last_name;
            //$user->setPassword(Yii::$app->security->generateRandomString());
            if($user->validate()){
                $user->save();
                $apple->user_id = $user->id;
                $apple->save();
                return [
                    'type' => 'temp',
                    'token' => $user->generateAccessToken()
                ];
            }else{
                throw new Exception(json_encode($user->errors));
            }
        }

    }


    public function actionAppleIdLogin(){
        $cache = \Yii::$app->cache;
        $cache->set('apple', ['ip'=>$this->getRealIpAddr(),'get'=>Yii::$app->request->get(),'post'=>Yii::$app->request->post()]);

      

        $post = Yii::$app->request->post();

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
            
            
            $url = Url::to( getenv('APPLE_LOGIN_FRONTEND') .'?'.http_build_query(['token'=>$token->getToken(),'apple_id'=>$user->getId()]));
            return $this->redirect( $url, 302);
        }
       
        throw new \yii\web\NotFoundHttpException('Not Found');
       
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
