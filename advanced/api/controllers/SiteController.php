<?php
namespace api\controllers;

use api\common\models\BindedEmailForm;
use api\modules\v1\models\data\Login;
use api\modules\v1\models\User;
use common\models\Invitation;
use common\models\PasswordResetRequestForm;
use common\models\ResendVerificationEmailForm;
use common\models\ResetPasswordForm;
use common\models\VerifyEmailForm;
use common\models\Wx;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="认证相关接口"
 * )
 */
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
    
    
    /**
     * 用户登录
     * 
     * @OA\Post(
     *     path="/site/login",
     *     summary="用户登录",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", description="用户名", example="admin"),
     *             @OA\Property(property="password", type="string", description="密码", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="JWT Token"),
     *             @OA\Property(property="id", type="integer", description="用户ID"),
     *             @OA\Property(property="username", type="string", description="用户名")
     *         )
     *     ),
     *     @OA\Response(response=400, description="登录失败"),
     *     @OA\Response(response=401, description="认证失败")
     * )
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $login = new Login();
        if ($login->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            if ($token = $login->login()) {
                $data = $login->user->toArray([],['auth']);
                $data['token'] = $token;
                return $data;
            } else {
                throw new Exception("Login Error", 400);
            }
        } else {
            throw new Exception(json_encode($login->getFirstErrors()), 400);
        }
    }
    
    public function actionWechatOpenid($token)
    {
        $wx = Wx::find()->where(['token' => $token])->one();
        if (isset($wx)) {
            $ret['token'] = $wx->token;
            $user = $wx->getUser()->one();
            if (isset($user)) {
                $ret['user'] = $user;
            }
            return $ret;
        }
        return ['code' => 20000];
        
    }
    public function actionWechatQrcode()
    {
        
        $wechat = \Yii::$app->wechat;
        $app = $wechat->officialAccount();
        $token = Yii::$app->security->generateRandomString();
        $result = $app->qrcode->temporary($token, 6 * 24 * 3600);
        $url = $app->qrcode->url($result['ticket']);
        
        return ['qrcode' => $url, 'token' => $token];
    }
    
    /**
    * Verify email address
    *
    * @param string $token
    * @throws BadRequestHttpException
    * @return yii\web\Response
    */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                $assignment = new Assignment($user->id);
                $invitation = Invitation::findOne(['recipient_id' => $user->id]);
                if ($invitation) {
                    $invitation->used = true;
                    $invitation->save();
                    if ($invitation->auth_item_name) {
                        $assignment->assign([$invitation->auth_item_name]);
                    } else {
                        $assignment->assign(['guest']);
                    }
                } else {
                    $assignment->assign(['guest']);
                }
                
                return ['data' => '您的email已经确认！', 'code' => 20000];
            }
        }
        throw new Exception(json_encode('抱歉，我们无法使用提供的令牌验证您的帐户。'), 400);
    }
    
    /**
    * Requests password reset.
    *
    * @return mixed
    */
    public function actionRequestPasswordReset()
    {
        
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                return ['data' => '检查您的email来进行进一步操作。', 'code' => 20000];
            } else {
                throw new Exception(json_encode('对不起，我们无法通过过您提供的email来重置密码。'), 400);
            }
        }
        
        if ($model->errors == null) {
            
            throw new Exception(json_encode('无法读取数据。'), 400);
        } else {
            throw new Exception(json_encode($model->errors), 400);
        }
    }
    /**
    * Resets password.
    *
    * @param string $token
    * @return mixed
    * @throws BadRequestHttpException
    */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            
            return ['data' => '更新密码成功。', 'code' => 20000];
        }
        
        if ($model->errors == null) {
            
            throw new Exception(json_encode('无法读取数据。'), 400);
        } else {
            throw new Exception(json_encode($model->errors), 400);
        }
    }
    
    /**
    * Resend verification email
    *
    * @return mixed
    */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                return ['data' => '检查您的email来进行进一步操作。', 'code' => 20000];
            }
            throw new Exception(json_encode('抱歉，我们无法为提供的电子邮件地址重新发送验证电子邮件。'), 400);
            
        }
        if ($model->errors == null) {
            throw new Exception(json_encode('无法读取数据。'), 400);
        } else {
            throw new Exception(json_encode($model->errors), 400);
        }
    }
    public function actionPasswordResetToken()
    {
        $get = Yii::$app->request->get();
        if (!isset($get['token'])) {
            throw new Exception(json_encode('无法读取token。'), 400);
        }
        
        $token = $get['token'];
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        
        return ['data' => '验证token成功。', 'code' => 20000];
    }
    public function actionError()
    {
        return Yii::$app->errorHandler->exception;
    }
    
    /**
     * 健康检查接口
     * 
     * @OA\Get(
     *     path="/site/health",
     *     summary="健康检查",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="系统健康状态",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="integer", example=1642694400),
     *             @OA\Property(property="datetime", type="string", example="2022-01-20 12:00:00"),
     *             @OA\Property(property="database", type="string", example="connected"),
     *             @OA\Property(property="cache", type="string", example="ok")
     *         )
     *     )
     * )
     * @return array
     */
    public function actionHealth()
    {
        $health = [
            'status' => 'ok',
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
        ];
        
        // 检查数据库连接
        try {
            Yii::$app->db->open();
            $health['database'] = 'connected';
        } catch (\Exception $e) {
            $health['database'] = 'disconnected';
            $health['status'] = 'error';
        }
        
        // 检查缓存
        try {
            if (Yii::$app->has('cache')) {
                Yii::$app->cache->set('health_check', true, 10);
                $health['cache'] = Yii::$app->cache->get('health_check') ? 'ok' : 'error';
            } else {
                $health['cache'] = 'not_configured';
            }
        } catch (\Exception $e) {
            $health['cache'] = 'error';
        }
        
        return $health;
    }
    
    /**
     * 版本号查询接口
     * 
     * @OA\Get(
     *     path="/site/version",
     *     summary="获取应用版本信息",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="版本信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="app_name", type="string", example="yiisoft/yii2-app-advanced"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="yii_version", type="string", example="2.0.51"),
     *             @OA\Property(property="php_version", type="string", example="8.4.0"),
     *             @OA\Property(property="environment", type="string", example="dev"),
     *             @OA\Property(property="debug", type="boolean", example=true)
     *         )
     *     )
     * )
     * @return array
     */
    public function actionVersion()
    {
        $composerFile = Yii::getAlias('@app/../composer.json');
        $version = '1.0.0'; // 默认版本号
        $appName = 'API Application';
        
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            if (isset($composer['name'])) {
                $appName = $composer['name'];
            }
            if (isset($composer['version'])) {
                $version = $composer['version'];
            }
        }
        
        return [
            'app_name' => $appName,
            'version' => $version,
            'yii_version' => Yii::getVersion(),
            'php_version' => PHP_VERSION,
            'environment' => YII_ENV,
            'debug' => YII_DEBUG,
        ];
    }
    
    public function actionMenu()
    {
        $callback = function ($menu) {
            $data = json_decode($menu['data'], true);
            $items = $menu['children'];
            $return = [
                'label' => $menu['name'],
                'url' => [$menu['route']],
            ];
            if ($data) {
                //visible
                isset($data['visible']) && $return['visible'] = $data['visible'];
                //icon
                isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
                //other attribute e.g. class...
                $return['options'] = $data;
            }
            (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'circle-o';
            $items && $return['items'] = $items;
            
            return $return;
        };
        
        $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        return $items;
    }
    
    public function actionBindedEmail($token)
    {
        $data = User::splitEmailVerificationTokenWithEmail($token);
        if ($data == null) {
            throw new Exception("无效Token", 400);
        }
        $model = new BindedEmailForm($token);
        $model->email = $data->email;
        
        if ($model->validate()) {
            $token = $model->binding();
            return [
                'access_token' => $token,
                'data' => '绑定邮件成功。',
                'code' => 20000,
            ];
        } else {
            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }
        }
        
    }
}
