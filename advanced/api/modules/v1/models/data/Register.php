<?php
namespace api\modules\v1\models\data;
use api\modules\v1\models\User;
use Yii;
use yii\base\Model;

/**
* Login form
*/

class Register extends Model
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
      ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_@.-]+$/', 'message' => 'Username can only contain letters, numbers, underscores, hyphens, @, and .'],
      ['password', 'string', 'min' => 4, 'max' => 20, 'message' => 'User Name must be between 4 and 20 characters long.'],
      [['username', 'password'], 'required'],
      ['password', 'string', 'min' => 8, 'max' => 20, 'message' => 'Password must be between 8 and 20 characters long.'],
      
      ['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', 'message' => 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character, and be at least 8 characters long.'],
      
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
  
  
  public function remove()
  {
    $this->_user->delete();
    $this->_user = null;
  }
  /**
  * Logs in a user using the provided username and password.
  *
  * @return bool whether the user is logged in successfully
  */
  public function create($email, $nickname)
  {
    
    if ($this->validate()) {
      $this->_user = new User();
      $this->_user->username = $this->username;
      $this->_user->setPassword($this->password);
      $this->_user->email = $email;
      $this->_user->nickname = $nickname;
      if($this->_user->validate()){
        $this->_user->save();
        $this->_user->addRoles(['user']);
        return true;
      }else{
        throw new \yii\base\Exception(json_encode($this->_user->errors));
      }
    } else {
      throw new \yii\base\Exception(json_encode($this->errors));
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
