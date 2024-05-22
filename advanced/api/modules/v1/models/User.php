<?php

namespace api\modules\v1\models;

use mdm\admin\components\Configs;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public $token = null;

    public function getUser()
    {
        $user = new \stdClass();
        $user->username = $this->username;
        $user->data = $this->getData();
        $user->roles = $this->getRoles();
        return $user;

    }
    public function fields()
    {
        //$fields = parent::fields();

        return [
            'id', 'nickname', 'email',
            'username',
        ];
    }

    public function getData()
    {

        $data = new \stdClass();
        $data->username = $this->username;
        $data->id = $this->id;
        $data->nickname = $this->nickname;
        $info = $this->userInfo;

        if ($info) {
            $data->info = $info->info;
            $data->avatar_id = $info->avatar_id;
            if ($info->avatar) {
                $data->avatar = $info->avatar;
            }
        }

        if ($this->email !== null) {
            $data->email = $this->email;
            $data->emailBind = true;
        } else if ($this->verification_token !== null) {
            $data->emailBind = false;
        } else {
            $data->email = null;
            $data->emailBind = false;
        }
        return $data;
    }

    public function generateAccessToken()
    {
        $jwt = \Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();

        $jwt_parameter = \Yii::$app->jwt_parameter;
        $token = $jwt->getBuilder()
            ->issuedBy($jwt_parameter->issuer) // Configures the issuer (iss claim)
            ->permittedFor($jwt_parameter->audience) // Configures the audience (aud claim)
            ->identifiedBy($jwt_parameter->id, true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 21600) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $this->id) // Configures a new claim, called "uid"
            ->getToken($signer, $key); // Retrieves the generated token

        return (string) $token;
    }
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);

    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);

    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     * @param \Lcobucci\JWT\Token $token
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::findIdentity($token->getClaim('uid'));
        $user->token = $token;
        return $user;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {

        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }
    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE,
        ]);
    }
    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public static function splitEmailVerificationTokenWithEmail($token)
    {

        $pattern = "/^.+_([0-9]+)@(.+@.+)$/";
        preg_match($pattern, $token, $matches);
        $ret = new \stdClass();
        $ret->email = $matches[2];
        $ret->timestamp = (int) $matches[1];
        return $ret;
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    public function generateEmailVerificationTokenWithEmail($email)
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time() . '@' . $email;
    }
    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByEmailVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
        ]);
    }
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Gets query for [[UserInfos]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUserInfo()
    {
        $has = $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
        $info = $has->one();
        if (!$info) {
            $info = new UserInfo();
            $info->user_id = $this->id;
            $info->save();
            $has = $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
        }
        return $has;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->userInfo->save();
        return true;
    }

    public function getRoles()
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($this->id);
        return array_values(array_map(function ($value) {return $value->roleName;}, $assignments));
    }

}
