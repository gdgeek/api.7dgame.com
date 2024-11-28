<?php
namespace api\modules\v1\models\data;
use api\modules\v1\models\User;
use yii\base\Model;

use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
/**
* Login form
*/

class Login extends Model
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
            [['username', 'password'], 'required'],
            ['username', 'validateUsername'],
            ['password', 'validatePassword'],
        ];
    }
    
    /**
    * {@inheritdoc}
    */
    public function attributeLabels()
    {
        return [
            'username' => 'User Name',
            'password' => 'Password',
        ];
    }
    
    
    public function validateUsername($attribute, $params)
    {
        
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError($attribute, 'There is no user.');
            }
        }
        
    }
    /**
    * Validates the password.
    * This method serves as the inline validation for password.
    *
    * @param string $attribute the attribute currently being validated
    * @param array $params the additional name-value pairs given in the rule
    */
    public function validatePassword($attribute, $params)
    {
        
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect password.');
            }
        }
        
    }
    
    /**
    * Logs in a user using the provided username and password.
    *
    * @return bool whether the user is logged in successfully
    */
    public function login()
    {
        
        if ($this->validate()) {
            $token = $this->_user->generateAccessToken();
            return $token;
        } else {
            return false;
        }
        
    }
    
    /**
    * Finds user by [[username]]
    *
    * @return User|null
    */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        
        return $this->_user;
    }
    
}
