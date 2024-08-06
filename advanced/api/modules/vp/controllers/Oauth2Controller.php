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
        $currentUrlWithoutParams2 = Yii::$app->request->getHostInfo() . '/' . Yii::$app->request->getPathInfo();
        return  $currentUrlWithoutParams2;
        return Url::current([], true);
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => 'com.voxelparty.www',
            'teamId'            => 'PK435YWZ25', // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => 'Q3R52SBK3X', // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => 'https://api.4mr.cn/vp/oauth2/apple-id-login',
        ]);
        if (!isset($_POST['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_POST['state']) || ($_POST['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            /** @var AppleAccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_POST['code']
            ]);

            // Optional: Now you have a token you can look up a users profile data
            // Important: The most details are only visible in the very first login!
            // In the second and third and ... ones you'll only get the identifier of the user!
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!', $user->getFirstName());

            } catch (Exception $e) {

                // Failed to get user details
                exit(':-(');
            }

            // Use this to interact with an API on the users behalf
            echo $token->getToken();
        }
    }
    public function actionClear(){
        $cache = \Yii::$app->cache;
        $cache->flush();
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

        $redirectUri = Yii::$app->request->getHostInfo() . '/' . Yii::$app->request->getPathInfo();
        \Firebase\JWT\JWT::$leeway = 60;
        
        $provider = new \League\OAuth2\Client\Provider\Apple([
            'clientId'          => getenv('APPLE_CLIENT_ID'), // com.voxelparty.www
            'teamId'            => getenv('APPLE_AUTH_TEAM_ID') , // 1A234BFK46 https://developer.apple.com/account/#/membership/ (Team ID)
            'keyFileId'         => getenv('APPLE_AUTH_KEY_IDs') , // 1ABC6523AA https://developer.apple.com/account/resources/authkeys/list (Key ID)
            'keyFilePath'       => getenv('APPLE_AUTH_KEY'), // __DIR__ . '/AuthKey_1ABC6523AA.p8' -> Download key above
            'redirectUri'       => $redirectUri,
        ]);
        if (!isset($_POST['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_POST['state']) || ($_POST['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            /** @var AppleAccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_POST['code']
            ]);

            // Optional: Now you have a token you can look up a users profile data
            // Important: The most details are only visible in the very first login!
            // In the second and third and ... ones you'll only get the identifier of the user!
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!', $user->getFirstName());

            } catch (Exception $e) {

                // Failed to get user details
                exit(':-(');
            }

            // Use this to interact with an API on the users behalf
            echo $token->getToken();
        }
        /*
        Yii::$app->response->format = Response::FORMAT_JSON;

        // 获取 POST 数据
        $code = Yii::$app->request->post('code');
        $idToken = Yii::$app->request->post('id_token');
        $state = Yii::$app->request->post('state');

        // 确保返回 HTTP 200 状态码
        Yii::$app->response->statusCode = 200;
        // 记录收到的数据用于调试
        //file_put_contents('apple_login.log', print_r(Yii::$app->request->post(), true), FILE_APPEND);
        $cache = \Yii::$app->cache;
        $cache->set('apple', ['ip'=>$this->getRealIpAddr(),'get'=>Yii::$app->request->get(),'post'=>Yii::$app->request->post()]);
        // 返回 HTTP 200 状态码和 JSON 响应
        return Yii::$app->response->redirect($state, 301);*/
       // return ['status' => 'success', 'message' => 'Data received successfully'];
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}
