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
       
        $tools = new JwtTools();
            
            // 你的 JWT 令牌
        $jwt = 'eyJraWQiOiJUOHRJSjF6U3JPIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLnZveGVscGFydHkud3d3IiwiZXhwIjoxNzIzMDk1Mjg1LCJpYXQiOjE3MjMwMDg4ODUsInN1YiI6IjAwMTg1MC5hY2I3NWFmMDg0MGU0NGQ2ODc4OWViYjAzMjBkOTc1YS4wODIyIiwiY19oYXNoIjoicEJoU3Q2XzM3ZnhSTHQwOVROWHpFUSIsImVtYWlsIjoiZGlydWkxQHllYWgubmV0IiwiZW1haWxfdmVyaWZpZWQiOnRydWUsImF1dGhfdGltZSI6MTcyMzAwODg4NSwibm9uY2Vfc3VwcG9ydGVkIjp0cnVlfQ.ZjPmVWEbFLdJ3nwGDLroZeGvfLVz_Qxf7bd3eb6brBlm2NRTpdeLzWiDKv4_uhJ2X9KdgbqmN3lHfEyAFRdhe92UIPaAwcDPgsh4abMCn37i7GWqOTF7i4isWyCmi-EyfDXoPu9GdpexwUwDUDfnmr0q3KPll0y2xq7yxu1TYO3ZaNj7rM3kJxKMQFo_hVVwe20292zeY3y6hyPCNxY382T_kMCQsErq6PllvS4ppEaVq4el9ZI9VITLDXdelPlV2EIXm1VhfIXnJzVjeJc-Q3Uhiv7kUYFgcag6P6UU7FMD0pcfRUwKjEGxSvkf4Zop6xIPjvCStBpE9DSN_l5rbA';
        $token = $tools->parse($jwt);
        $datas = $token->claims()->all();
        return $datas;
        /*$key = Yii::$app->jwt_tools->buildKey( [
            'key' =>  getenv('APPLE_AUTH_KEY'), // path to your PRIVATE key, you can start the path with @ to indicate this is a Yii alias
            'passphrase' => '', // omit it if you are not adding any passphrase
            'method' => \bizley\jwt\Jwt::METHOD_FILE,
        ]);
       $conf = Configuration::forSymmetricSigner(
            new Signer\Ecdsa\Sha256(),
            $key
        );

        $constraints = $conf->validationConstraints();

        //$token = $jwt instanceof Token ? $jwt : $this->parse($jwt);
        return $conf->validator()->validate($token, ...$constraints);
     */
        
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
    


    //https://appleid.apple.com/auth/authorize?client_id=com.voxelparty.www&redirect_uri=https%3A%2F%2Fapi.voxelparty.com%2Fvp%2Foauth2%2Fapple-id-login&response_type=code%20id_token&state=your-state&scope=name%20email&response_mode=web_message&frame_id=4de2e626-da69-4b09-a8fd-54a9a710def1&m=12&v=1.5.5
    //https://appleid.apple.com/auth/authorize?scope=email&state=a9583c14408af68ac05cbfed3a8274ef&response_type=code&approval_prompt=auto&redirect_urihttps%3A%2F%2Fapi.voxelparty.com%2Fvp%2Foauth2%2Fapple-id-login&client_id=com.voxelparty.www&response_mode=form_post
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
            
            $data = [
                'email'=>$user->getEmail(),
                'last'=>$user->getLastName(),
                'first'=>$user->getFirstName(),
                'id'=>$user->getId(),
            ];
            return  [
                "token" => $token->getToken(),
                "data" => $data,
                "post" => $post,
                "all" => $all,
            ];
        }
        /*
        if (!isset($_POST['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $session['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_POST['state']) || ($_POST['state'] !== $session['oauth2state'])) {

            unset($session['oauth2state']);

            exit($_POST['state'] . ':' . $session['oauth2state']);

        } else {

            // Try to get an access token (using the authorization code grant)
          
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_POST['code']
            ]);


 
            // Optional: Now you have a token you can look up a users profile data
            // Important: The most details are only visible in the very first login!
            // In the second and third and ... ones you'll only get the identifier of the user!
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);
                $data = [
                    'email'=>$user->getEmail(),
                    'last'=>$user->getLastName(),
                    'first'=>$user->getFirstName(),
                    'id'=>$user->getId(),
                ];
               // throw new \Exception('Hello %s!', $user->getFirstName());
                // Use these details to create a new profile
              // printf('Hello %s!', $user->getFirstName());

            } catch (Exception $e) {
                throw new \Exception(':-(');
                // Failed to get user details
               // exit(':-(');
            }
            
            // Use this to interact with an API on the users behalf
            return  [
                "token"=>$token->getToken(),
                "data"=> $data,
            ];
        }
     */
       
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
