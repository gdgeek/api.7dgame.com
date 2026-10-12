<?php

namespace backend\models;

// Loaded by IdentityAdminUserController; preserve MDM form names and field names.
class IdentityAdminLoginForm extends \common\models\LoginForm
{
    public function formName() { return 'Login'; }
}
class IdentityAdminPasswordRequestForm extends \common\models\PasswordResetRequestForm
{
    public function formName() { return 'PasswordResetRequest'; }
}
class IdentityAdminResetForm extends \common\models\ResetPasswordForm
{
    public $retypePassword;
    public function formName() { return 'ResetPassword'; }
    public function rules() { return array_merge(parent::rules(), [['retypePassword', 'required'], ['retypePassword', 'compare', 'compareAttribute' => 'password']]); }
}
class IdentityAdminSignupForm extends \yii\base\Model
{
    public $username;
    public $email;
    public $password;
    public $retypePassword;
    public function formName() { return 'Signup'; }
    public function rules() { return [[['username', 'email'], 'trim'], [['username', 'email', 'password', 'retypePassword'], 'required'], ['email', 'email'], ['username', 'string', 'min' => 2, 'max' => 255], ['retypePassword', 'compare', 'compareAttribute' => 'password']]; }
}
class IdentityAdminChangePasswordForm extends \yii\base\Model
{
    public $oldPassword;
    public $newPassword;
    public $retypePassword;
    public function formName() { return 'ChangePassword'; }
    public function rules() { return [[['oldPassword', 'newPassword', 'retypePassword'], 'required'], ['retypePassword', 'compare', 'compareAttribute' => 'newPassword']]; }
}
