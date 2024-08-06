<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
use yii\rest\ActiveController;
use Yii;

use yii\helpers\Url;
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
      
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => getenv('APPLE_CLIENT_ID'), // com.voxelparty.www
            'teamId'            => getenv('APPLE_AUTH_TEAM_ID') , // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => getenv('APPLE_AUTH_KEY_ID') , // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => getenv('APPLE_REDIRECT_URI'),
        ]);
        $token = $provider->getAccessToken('authorization_code', [
            'code' => 'c5ee72c7b64ce40c0b71efabb26a20c9c.0.rryvq.92i6mLsmTmulrADiP-FiNg'
        ]);
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
        $redirectUri = Yii::$app->request->getHostInfo() . '/' . Yii::$app->request->getPathInfo();
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => getenv('APPLE_CLIENT_ID'), // com.voxelparty.www
            'teamId'            => getenv('APPLE_AUTH_TEAM_ID') , // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => getenv('APPLE_AUTH_KEY_ID') , // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => getenv('APPLE_REDIRECT_URI'),
        ]);
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
            /** @var AppleAccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_POST['code']
            ]);
/*
<state>d7b9f5a80202edc6db5cfbf41e8362ce</state>
<code>c5ee72c7b64ce40c0b71efabb26a20c9c.0.rryvq.92i6mLsmTmulrADiP-FiNg</code>
 */
            // Optional: Now you have a token you can look up a users profile data
            // Important: The most details are only visible in the very first login!
            // In the second and third and ... ones you'll only get the identifier of the user!
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

               // throw new \Exception('Hello %s!', $user->getFirstName());
                // Use these details to create a new profile
              // printf('Hello %s!', $user->getFirstName());

            } catch (Exception $e) {
                throw new \Exception(':-(');
                // Failed to get user details
               // exit(':-(');
            }
            
            // Use this to interact with an API on the users behalf
            return  $token->getToken();
        }
       
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
