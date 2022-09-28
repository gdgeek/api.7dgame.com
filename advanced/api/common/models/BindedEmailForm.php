<?php
namespace api\common\models;

use api\modules\v1\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Signup form
 */
class BindedEmailForm extends Model
{
    public $email;

    /**
     * @var \api\modules\v1\models\User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {

        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        $this->_user = User::findByEmailVerificationToken($token);

        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        parent::__construct($config);

    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This email address has already been taken.'],

        ];
    }
    public function binding()
    {
        if ($this->validate()) {
            $this->_user->email = $this->email;
            $this->_user->verification_token = null;
            $this->_user->save();
            $token = $this->_user->generateAccessToken();
            return $token;
        } else {
            return false;
        }
    }

}
