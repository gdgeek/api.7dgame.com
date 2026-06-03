<?php
namespace api\common\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use api\modules\v1\models\User;
use common\components\security\PasswordPolicyValidator;

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
            ['oldPassword', 'string'],
            ['password', 'string', 'min' => 8, 'max' => 64,
                'tooShort' => '密码长度不能少于 8 个字符',
                'tooLong' => '密码长度不能超过 64 个字符'],
            ['password', 'validatePasswordPolicy'],
        ];
    }

    /**
     * 使用统一密码策略验证新密码；oldPassword 只用于哈希比对。
     */
    public function validatePasswordPolicy($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $validator = new PasswordPolicyValidator();
        $result = $validator->validate($this->$attribute, [
            'username' => $this->_user->username ?? null,
            'email' => $this->_user->email ?? null,
        ]);

        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $this->addError($attribute, $error);
            }
        }
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
