<?php
namespace api\modules\v1\models\data;
use api\modules\v1\models\User;
use common\components\security\PasswordPolicyValidator;
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
      [['username', 'password'], 'required'],
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
      'email' => $this->username,
    ]);

    if (!$result['valid']) {
      foreach ($result['errors'] as $error) {
        $this->addError($attribute, $error);
      }
    }
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
