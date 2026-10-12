<?php

namespace backend\controllers;

use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\IndependentIdentity;
use backend\models\IdentityAdminUserSearch;
use backend\models\IdentityAdminUserView;
use common\components\IdentityLegacyAdmin;
use Yii;

require_once dirname(__DIR__) . '/models/IdentityAdminAccountForms.php';

/** Retain MDM's views while every account operation terminates at Identity. */
class IdentityAdminUserController extends \mdm\admin\controllers\UserController
{
    public function getViewPath() { return Yii::getAlias('@mdm/admin/views/user'); }
    public function actionIndex()
    {
        if (!IndependentIdentity::enabled()) return parent::actionIndex();
        $search = new IdentityAdminUserSearch();
        return $this->render('index', ['searchModel' => $search, 'dataProvider' => $search->search(Yii::$app->request->queryParams)]);
    }
    protected function findModel($id)
    {
        if (!IndependentIdentity::enabled()) return parent::findModel($id);
        return new IdentityAdminUserView(IdentityLegacyAdmin::call('user-view', ['id' => (int)$id]));
    }
    public function actionDelete($id)
    {
        if (!IndependentIdentity::enabled()) return parent::actionDelete($id);
        IdentityLegacyAdmin::call('user-delete', ['id' => (int)$id]);
        return $this->redirect(['index']);
    }
    public function actionActivate($id)
    {
        if (!IndependentIdentity::enabled()) return parent::actionActivate($id);
        IdentityLegacyAdmin::call('user-activate', ['id' => (int)$id]);
        return $this->goHome();
    }
    public function actionLogin()
    {
        if (!IndependentIdentity::enabled()) return parent::actionLogin();
        if (!Yii::$app->user->isGuest) return $this->goHome();
        $model = new \backend\models\IdentityAdminLoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) return $this->goBack();
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }
    public function actionSignup()
    {
        if (!IndependentIdentity::enabled()) return parent::actionSignup();
        $model = new \backend\models\IdentityAdminSignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                IdentityLegacyAdmin::call('user-create', ['username' => $model->username, 'email' => $model->email, 'password' => $model->password,
                    'idempotencyKey' => Yii::$app->request->headers->get('Idempotency-Key')]);
                return $this->goHome();
            } catch (\yii\web\BadRequestHttpException $exception) {
                $model->addError('username', $exception->getMessage());
            }
        }
        return $this->render('signup', ['model' => $model]);
    }
    public function actionRequestPasswordReset()
    {
        if (!IndependentIdentity::enabled()) return parent::actionRequestPasswordReset();
        $model = new \backend\models\IdentityAdminPasswordRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
            return $this->goHome();
        }
        return $this->render('requestPasswordResetToken', ['model' => $model]);
    }
    public function actionResetPassword($token)
    {
        if (!IndependentIdentity::enabled()) return parent::actionResetPassword($token);
        $model = new \backend\models\IdentityAdminResetForm($token);
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');
            return $this->goHome();
        }
        return $this->render('resetPassword', ['model' => $model]);
    }
    public function actionChangePassword()
    {
        if (!IndependentIdentity::enabled()) return parent::actionChangePassword();
        $model = new \backend\models\IdentityAdminChangePasswordForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $access = Yii::$app->user->identityAccessToken();
            if ($access === null) throw new \yii\web\UnauthorizedHttpException('An Identity login session is required.');
            $result = Yii::createObject(IdentityProviderClient::class)->proxyAccountLifecycle('POST', '/v1/password/change', [
                'old_password' => $model->oldPassword, 'new_password' => $model->newPassword, 'confirm_password' => $model->retypePassword,
            ], [], ['authorization' => 'Bearer ' . $access]);
            if ($result['status'] >= 200 && $result['status'] < 300) return $this->goHome();
            $model->addError('newPassword', 'Identity rejected the password change.');
        }
        return $this->render('change-password', ['model' => $model]);
    }
}
