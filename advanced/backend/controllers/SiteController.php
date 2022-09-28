<?php
namespace backend\controllers;

use common\models\Invitation;
use common\models\PasswordResetRequestForm;
use common\models\ResendVerificationEmailForm;
use common\models\ResetPasswordForm;
use common\models\SignupForm;
use common\models\VerifyEmailForm;
use api\modules\v1\models\User;
use common\models\LoginForm;
use mdm\admin\models\Assignment;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $enableCsrfValidation = false;
    public $layout = '@backend/views/layouts/site/main.php';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }
   
    public function actionLanguage($language)
    {
        $session = Yii::$app->session;
        $session->open();
        if (isset($language)) {
            Yii::$app->session['language'] = $language;
        }
        $this->goBack(Yii::$app->request->headers['Referer']);
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        Yii::info("info .... ", "rhythmk");


        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }

    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            $invitation = Invitation::findOne(['code' => $model->invitation]);

            if ($invitation != null) {
                if (!$invitation->used) {
                    if ($model->signup()) {
                        echo json_encode($model->username);

                        $user = User::findOne(['username' => $model->username, 'status' => User::STATUS_INACTIVE]);

                        $invitation->recipient_id = $user->id;
                        $invitation->save();
                        Yii::$app->session->setFlash('success', '感谢您的注册。请检查您的收件箱中的验证邮件。');
                        return $this->goHome();
                    }
                } else {

                    Yii::$app->session->setFlash('error', '邀请码已经被使用过了。');
                }
            } else {

                Yii::$app->session->setFlash('error', '邀请码输入错误。');
            }

        }

        return $this->render('signup', [
            'model' => $model,
        ]);
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
                Yii::$app->session->setFlash('success', '检查您的email来进行进一步操作。');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', '对不起，我们无法通过过您提供的email来重置密码。');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
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
            Yii::$app->session->setFlash('success', '更新密码成功。');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
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

                Yii::$app->session->setFlash('success', '您的email已经确认！');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', '抱歉，我们无法使用提供的令牌验证您的帐户。');
        return $this->goHome();
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
                Yii::$app->session->setFlash('success', '检查您的email来进行进一步操作。');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', '抱歉，我们无法为提供的电子邮件地址重新发送验证电子邮件。');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model,
        ]);
    }

}
