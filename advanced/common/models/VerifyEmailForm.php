<?php

namespace common\models;

use api\modules\v1\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            $this->token = $token;
            parent::__construct($config);
            return;
        }
        $this->_user = User::findByVerificationToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail()
    {
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            $data = (new IdentityLegacyFormBridge())->operation('verify', ['token' => $this->token]);
            $id = $data['userId'] ?? null;
            if (!is_int($id) || $id <= 0) {
                throw new \common\components\security\ServiceUnavailableHttpException('Identity returned no verified account.');
            }
            if (is_array($data['authTokens'] ?? null) && Yii::$app->user instanceof \common\components\IdentityBackendUser) {
                Yii::$app->user->loginWithIdentityTokens($data['authTokens']);
            }
            return \api\modules\v1\services\IndependentIdentity::authority()->identityById($id);
        }
        $user = $this->_user;
        $user->status = User::STATUS_ACTIVE;
        return $user->save(false) ? $user : null;
    }
}
