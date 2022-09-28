<?php
namespace common\models;

use api\modules\v1\models\User;
use yii\base\Model;

/**
 * Signup form
 */
class WechatSignupForm extends Model
{
    public $username;
    public $password;
    public $nickname;
    // public $avatar;
    public $wx_openid;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['wx_openid', 'required'],
            ['wx_openid', 'string'],
            ['wx_openid', 'required'],
            ['wx_openid', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This openid has already been taken.'],

            ['nickname', 'required'],
            ['nickname', 'string'],

            //  ['avatar', 'string'],
        ];
    }
    public function getUser(): ?User
    {
        return $this->_user;
    }
    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $this->_user = new User();
        $this->_user->username = $this->username;
        $this->_user->nickname = $this->nickname;
        //  $this->_user->avatar = $this->avatar;
        $this->_user->wx_openid = $this->wx_openid;
        $this->_user->status = User::STATUS_ACTIVE;
        $this->_user->setPassword($this->password);

        $this->_user->generateAuthKey();
        $this->_user->generateEmailVerificationToken();

        if ($this->_user->validate() && $this->_user->save()) {
            return true;
        }

        return false;
    }

}
