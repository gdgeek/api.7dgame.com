<?php
namespace api\common\models;

use yii\base\InvalidArgumentException;
use api\modules\v1\models\User;
use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class BindEmailForm extends Model
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
           
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\api\modules\v1\models\User', 'message' => 'This email address has already been taken.'],

           

        ];
    }
    public function binding($token){
      
    }
    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    public function sendEmail()
    {
        $user = $this->_user;
        $user->generateEmailVerificationTokenWithEmail($this->email);
       // $user->email = $this->email;
        $user->save();
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailBind-html', 'text' => 'emailBind-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('在 ' . Yii::$app->name . ' 上绑定邮箱')
            ->send();
    }
}
