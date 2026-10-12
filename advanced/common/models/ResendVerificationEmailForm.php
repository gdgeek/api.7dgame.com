<?php


namespace common\models;

use Yii;
use api\modules\v1\models\User;
use yii\base\Model;

class ResendVerificationEmailForm extends Model
{
    /**
     * @var string
     */
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            return [['email', 'trim'], ['email', 'required'], ['email', 'email']];
        }
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\api\modules\v1\models\User',
                'filter' => ['status' => User::STATUS_INACTIVE],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends confirmation email to user
     *
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            return (new IdentityLegacyFormBridge())->sendEmail('resend-verification', ['email' => $this->email]);
        }
        $user = User::findOne([
            'email' => $this->email,
            'status' => User::STATUS_INACTIVE
        ]);

        if ($user === null) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' 管理员'])
            ->setTo($this->email)
            ->setSubject('在 ' . Yii::$app->name. '注册账号')
            ->send();
    }
}
