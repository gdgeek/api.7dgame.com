<?php
namespace api\common\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use api\modules\v1\models\User;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password;
    public $oldPassword;

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
    public function __construct($user, $config = [])
    {
       
        $this->_user = $user;
        
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong user.');
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'oldPassword'], 'required'],
            [['password', 'oldPassword'], 'string', 'min' => 6]
        ];
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        if(!$user->validatePassword($this->oldPassword)){
            throw new InvalidArgumentException('Wrong oldPassword.');
        }else{
            $user->setPassword($this->password);
            return $user->save(false);
        }
    }
}
