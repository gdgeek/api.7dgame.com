<?php
namespace common\models;

use Yii;
use yii\base\Model;
use api\modules\v1\models\User;
use common\components\security\PasswordPolicyValidator;
/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $invitation;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 8, 'max' => 64,
                'tooShort' => '密码长度不能少于 8 个字符',
                'tooLong' => '密码长度不能超过 64 个字符'],
            ['password', 'validatePasswordPolicy'],

			
            ['invitation', 'required'],


        ];
        return \api\modules\v1\services\IndependentIdentity::enabled()
            ? array_values(array_filter($rules, static fn($rule) => $rule[1] !== 'unique')) : $rules;
    }

    /**
     * 使用 PasswordPolicyValidator 验证密码强度（仅新注册用户）
     */
    public function validatePasswordPolicy($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        $validator = new PasswordPolicyValidator();
        $result = $validator->validate($this->$attribute, [
            'username' => $this->username,
            'email' => $this->email,
        ]);
        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $this->addError($attribute, $error);
            }
        }
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            return (new IdentityLegacyFormBridge())->sendEmail('signup', [
                'username' => $this->username, 'email' => $this->email, 'password' => $this->password, 'invitation' => $this->invitation,
            ]);
        }
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
       // $user->generateEmailVerificationToken();

        if($this->sendEmail($user)){
            if($user->save())
            {
                return true;
            }
        }

         

        return false;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('在 ' . Yii::$app->name .' 上注册新的账户')
            ->send();
    }
}
