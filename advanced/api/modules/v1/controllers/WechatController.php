<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use common\models\Wx;
use api\modules\v1\models\Wechat;
use api\modules\v1\models\User;
use api\modules\v1\services\AccountLifecycleProxyService;
use Yii;
use OpenApi\Annotations as OA;
use yii\web\ServiceUnavailableHttpException;

/**
 * @OA\Tag(
 *     name="Wechat",
 *     description="微信相关接口"
 * )
 */
class WechatController extends \yii\rest\Controller
{
    private ?AccountLifecycleProxyService $accountLifecycleProxy = null;

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }

    private function isLocalDeployment()
    {
        $mode = strtolower(getenv('DEPLOYMENT_MODE') ?: 'cloud');
        return in_array($mode, ['local', 'private'], true);
    }

    private function featureEnabled($name)
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return !$this->isLocalDeployment();
        }
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private function disabledWechatResponse()
    {
        if ($this->featureEnabled('ENABLE_WECHAT_LOGIN') && $this->wechatConfigured()) {
            return null;
        }
        Yii::$app->response->statusCode = 501;
        return [
            'code' => 'FEATURE_DISABLED',
            'feature' => 'wechat-login',
            'message' => '微信登录在当前部署模式下已禁用',
        ];
    }

    private function wechatConfigured(): bool
    {
        $appId = getenv('WECHAT_APP_ID') ?: getenv('WECHAT_APPID');
        $secret = getenv('WECHAT_SECRET');
        $token = getenv('WECHAT_TOKEN');

        return is_string($appId) && trim($appId) !== ''
            && is_string($secret) && trim($secret) !== ''
            && is_string($token) && trim($token) !== '';
    }

    public function actionQrcode()
    {
        if ($disabled = $this->disabledWechatResponse()) {
            return $disabled;
        }

        try {
            $wechat = Yii::$app->wechat;
            $app = $wechat->application();
            $api = $app->getClient();
            $lifetime = 6 * 24 * 3600;
            $token = Yii::$app->security->generateRandomString();
            $payload = [
                "expire_seconds" => $lifetime,
                "action_name" => "QR_STR_SCENE",
                "action_info" => [
                    "scene" => ["scene_str" => $token],
                ],
            ];

            $response = $api->post('/cgi-bin/qrcode/create', ['body' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
            $result = $response->toArray();
        } catch (\Throwable $error) {
            Yii::error(['wechatQrcodeError' => $error->getMessage()], 'wechat.login');
            throw new ServiceUnavailableHttpException('wechat qrcode service unavailable');
        }
        if (!isset($result['ticket'])) {
            Yii::error(['wechatQrcode' => $result], 'wechat.login');
            throw new ServiceUnavailableHttpException('wechat qrcode ticket missing');
        }

        $ticket = (string)$result['ticket'];
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . rawurlencode($ticket);

        return [
            'qrcode' => [
                'url' => $url,
                'ticket' => $ticket,
            ],
            'token' => $token,
            'lifetime' => $lifetime,
        ];
    }

    public function actionRefresh()
    {
        if ($disabled = $this->disabledWechatResponse()) {
            return $disabled;
        }

        $token = Yii::$app->request->get("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }

        $wechat = $this->findWechatRecord((string)$token);
        if (!$wechat) {
            return ['success' => false, 'message' => "pending"];
        }

        return [
            'success' => true,
            'message' => $this->wechatUser($wechat) ? "signin" : "signup",
            'token' => $token,
        ];
    }

    /**
     * @OA\Post(
     *     path="/v1/wechat/login",
     *     summary="微信登录",
     *     description="使用微信 token 进行登录",
     *     tags={"Wechat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", description="微信 token", example="wx_token_123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="login"),
     *             @OA\Property(property="token", type="string", description="JWT Token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误 - token 缺失或微信账号不存在")
     * )
     */
    public function actionLogin(){
        if ($disabled = $this->disabledWechatResponse()) {
            return $disabled;
        }
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $wechat = $this->findWechatRecord((string)$token);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");

        }
        $user = $this->wechatUser($wechat);
        if($user){
            return ['success' => true, 'message' => "login", 'token'=> $user->token()];
        }else{
            throw new BadRequestHttpException('Login failed');
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/wechat/register",
     *     summary="微信注册",
     *     description="使用微信 token 注册新用户",
     *     tags={"Wechat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "username", "password"},
     *             @OA\Property(property="token", type="string", description="微信 token", example="wx_token_123456"),
     *             @OA\Property(property="username", type="string", description="用户名", example="newuser"),
     *             @OA\Property(property="password", type="string", description="密码", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="注册成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="register"),
     *             @OA\Property(property="uid", type="integer", description="用户ID"),
     *             @OA\Property(property="token", type="string", description="JWT Token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误 - 参数缺失或已注册")
     * )
     */
    public function actionRegister(){
        if (($proxy = $this->proxyAccountLifecycle('/v1/wechat/register')) !== null) {
            return $proxy['body'];
        }

        if ($disabled = $this->disabledWechatResponse()) {
            return $disabled;
        }
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $username = Yii::$app->request->post("username");
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        $password = Yii::$app->request->post("password");
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        $wechat = $this->findWechatRecord((string)$token);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");
        }
        $linkedUser = $this->wechatUser($wechat);
        if($linkedUser){

            throw new BadRequestHttpException("already registered,". $linkedUser->id);
        }else{
            $user = User::create($username, $password);
            if ($wechat instanceof Wx && $wechat->wx_openid) {
                $user->wx_openid = $wechat->wx_openid;
            }
            if(!$user->validate()){
                throw new BadRequestHttpException(json_encode($user->errors));
            }
            $user->save();
            $user->addRoles(["user"]);
           
            $wechat->user_id = $user->id;
           if(!$wechat->validate() ){
                throw new BadRequestHttpException(json_encode($wechat->errors, true));
            }
            $wechat->save();
            return ['success' => true, 'message' => "register", 'uid'=>$user->id, 'token'=> $user->token()];
           
        }
    }

    private function findWechatRecord(string $token)
    {
        $wechat = Wechat::findOne(['token' => $token]);
        if ($wechat) {
            return $wechat;
        }

        return Wx::find()->where(['token' => $token])->one();
    }

    private function wechatUser($wechat): ?User
    {
        $user = $wechat->user ?? null;
        return $user instanceof User ? $user : null;
    }

    private function proxyAccountLifecycle(string $path): ?array
    {
        if ($this->accountLifecycleProxy === null) {
            $this->accountLifecycleProxy = new AccountLifecycleProxyService();
        }

        return $this->accountLifecycleProxy->proxyCurrentRequest('register', $path);
    }
  


}
