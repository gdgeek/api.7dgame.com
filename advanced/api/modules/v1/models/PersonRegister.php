<?php
namespace api\modules\v1\models;

use api\modules\v1\models\User;
use common\components\security\PasswordPolicyValidator;
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
            ['password', 'string', 'min' => 8, 'max' => 64,
                'tooShort' => '密码长度不能少于 8 个字符',
                'tooLong' => '密码长度不能超过 64 个字符'],
            ['password', 'validatePasswordPolicy'],
            
        ];
    }

    /**
    * 使用统一密码策略验证新密码。
    */
    public function validatePasswordPolicy($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $validator = new PasswordPolicyValidator();
        $result = $validator->validate($this->$attribute, [
            'username' => $this->username,
        ]);

        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $this->addError($attribute, $error);
            }
        }
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
