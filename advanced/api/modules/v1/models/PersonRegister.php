<?php
namespace api\modules\v1\models;

use api\modules\v1\models\User;
use yii\base\Model;

/**
 * Signup form
 */
class PersonRegister extends Model
{
    public $username;
    public $password;

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

        ];
    }

    public function getUser(): ?User
    {
        return $this->_user;
    }
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $this->_user = new User();
        $this->_user->username = $this->username;
        $this->_user->status = User::STATUS_ACTIVE;
        $this->_user->setPassword($this->password);
        $this->_user->save();

        return true;
    }

}
