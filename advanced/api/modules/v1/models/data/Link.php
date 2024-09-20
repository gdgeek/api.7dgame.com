<?php
namespace api\modules\v1\models\data;
use api\modules\v1\models\User;
use Yii;
use yii\base\Model;

/**
* Login form
*/

class Link extends Model
{
  public $username;
  public $password;
  
  public $_user;
  
  /**
  * {@inheritdoc}
  */
  public function rules()
  {
    return [
      [['username', 'password'], 'required']
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
  
  
  /**
  * Logs in a user using the provided username and password.
  *
  * @return bool whether the user is logged in successfully
  */
  public function bind()
  {
    if ($this->validate()) {
      $this->_user = User::findByUsername($this->username);
      if (!$this->_user || !$this->_user->validatePassword($this->password)) {
        throw new \yii\web\UnauthorizedHttpException('Incorrect username or password.');
      }
      return true;
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
