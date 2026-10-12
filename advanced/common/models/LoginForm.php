<?php
namespace common\models;

use Yii;
use yii\base\Model;
use api\modules\v1\models\User;
/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
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
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
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
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            if (!$this->validate(['username', 'rememberMe']) || !is_string($this->password) || $this->password === '') {
                $this->addError('password', 'Password cannot be blank.');
                return false;
            }
            try {
                $tokens = (new \api\modules\v1\services\IdentityProviderClient())->login($this->username, $this->password);
                return Yii::$app->user->loginWithIdentityTokens($tokens, $this->rememberMe ? 3600 * 24 * 30 : 0);
            } catch (\yii\web\UnauthorizedHttpException $exception) {
                $this->addError('password', 'Incorrect username or password.');
                return false;
            }
        }
        if ($this->validate()) {
            $user = $this->getUser();
            $access_token = $user->generateAccessToken();
            $user->save();
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
