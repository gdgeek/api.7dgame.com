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
