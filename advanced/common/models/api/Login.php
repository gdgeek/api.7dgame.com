<?php
namespace common\models\api;

use yii\base\Model;

/**


class Login extends Model
{
public $username;
public $password;

private $_user;

public function rules()
{
return [
// username and password are both required
[['username', 'password'], 'required'],
// password is validated by validatePassword()
['password', 'validatePassword'],
];
}


public function validatePassword($attribute, $params)
{
if (!$this->hasErrors()) {
$user = $this->getUser();
if (!$user || !$user->validatePassword($this->password)) {
$this->addError($attribute, 'Incorrect username or password.');
}
}

}


public function login()
{
if ($this->validate()) {
$token = $this->_user->generateAccessToken();
return $token;
} else {
return false;
}

}



public function getUser()
{
if ($this->_user === null) {
$this->_user = User::findByUsername($this->username);
}

return $this->_user;
}

}
 */
