<?php

namespace api\controllers;

use api\modules\v1\models\Login;
use api\modules\v1\models\User;
use common\models\Invitation;
use common\models\PasswordResetRequestForm;
use common\models\ResendVerificationEmailForm;
use common\models\ResetPasswordForm;
use common\models\SignupForm;
use common\models\VerifyEmailForm;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class UserController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\User';

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

    public function actions()
    {
        $actions = parent::actions();

        // 禁用 "delete" 和 "create" 动作
        unset($actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
            $token = $model->login();
            if ($token) {
                return [
                    'access_token' => $token,
                ];
            } else {
                throw new Exception(json_encode("用户名或密码错误"), 400);
            }
        } else {
            throw new Exception(json_encode($model->getFirstErrors()), 400);
        }
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {

        $model = new SignupForm();
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            $invitation = Invitation::findOne(['code' => $model->invitation]);
            if (null == $invitation) {
                throw new Exception(json_encode("邀请码输入错误。"), 400);
            }
            if ($invitation->used) {
                throw new Exception(json_encode('邀请码已经被使用过了。'), 400);
            }
            $model->signup();
            $user = User::findOne(['username' => $model->username, 'status' => User::STATUS_INACTIVE]);
            $invitation->recipient_id = $user->id;
            $invitation->save();
            return ['data' => '感谢您的注册。请检查您的收件箱中的验证邮件。'];
        } else {

            throw new Exception(json_encode($model->errors), 400);

        }
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
                        $assignment->assign(['user']);
                    }
                } else {
                    $assignment->assign(['user']);
                }

                return ['data' => '您的email已经确认！'];
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
                return ['data' => '检查您的email来进行进一步操作。'];
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

            return ['data' => '更新密码成功。'];
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
                return ['data' => '检查您的email来进行进一步操作。'];
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

        return ['data' => '验证token成功。'];
    }
    public function actionTest()
    {

        return Yii::$app->name;

    }

}
