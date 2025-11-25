<?php

namespace api\modules\v1\models;

use api\modules\v1\RefreshToken;
use yii\db\Expression;
use yii\caching\TagDependency;
use mdm\admin\models\Assignment;
use mdm\admin\components\Configs;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
//清理干净的 user 模型
/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $auth_key
 * @property string|null $password_hash
 * @property string|null $password_reset_token
 * @property string|null $email
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $nickname
 *
 * @property File[] $files //文件
 * @property UserInfo[] $userInfo //用户信息
 * @property Verse[] $verses
 * @property Token[] $vpTokens 游戏 token
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{


    public function afterSave($insert, $changedAttributes)
    {

        parent::afterSave($insert, $changedAttributes);


        if (!$this->userInfo) {
            $info = new UserInfo();
            $info->user_id = $this->id;
            $info->save();
        }
        TagDependency::invalidate(Yii::$app->cache, 'user_cache');
    }

    public static function findByAuthKey($authKey)
    {
        return static::find()->where(['auth_key' => $authKey])->one();
    }

    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->one();
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $claims = Yii::$app->jwt->parse($token)->claims();
        $uid = $claims->get('uid');
        $user = static::findIdentity($uid);
        return $user;
    }
    public function getId()
    {
        return $this->id;
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
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }


    public function fields()//返回的数据？
    {

        return [
            'nickname',
            'username',
            'roles'
        ];
    }
    public function extraFields()
    {
        return ['auth'];
    }

    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }
    /**
     * Gets query for [[UserInfo]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'id']);

    }


    public static function findByRefreshToken($refreshToken)
    {

        $token = RefreshToken::find()->where(['key' => $refreshToken])->one();

        if (!$token) {
            throw new \yii\web\UnauthorizedHttpException('Refresh token is invalid.');
        }
        $user = static::findIdentity($token->user_id);
        if (!$user) {
            throw new \yii\web\UnauthorizedHttpException('User is not found.');
        }
        return $user;
    }
    public function getRefreshToken()
    {
        return $this->hasOne(RefreshToken::className(), ['user_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
    public function token()
    {
        $token = new RefreshToken();
        $token->user_id = $this->id;

        $token->key = Yii::$app->security->generateRandomString();
        $token->save();
        $now = new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone));
        $expires = $now->modify('+3 hour');
        return [
            'accessToken' => $this->generateAccessToken($now, $expires),
            'expires' => $expires->format('Y-m-d H:i:s'),
            'refreshToken' => $token->key,
        ];
    }
    public function getAuth()
    {
        return $this->auth_key;
    }
    public function getRoles()
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($this->id);
        return array_values(array_map(function ($value) {
            return $value->roleName;
        }, $assignments));

    }

    public function getData()
    {

        return $this->toArray(['username', 'nickname']);

    }

    //生成token
    public function generateAccessToken($now = null, $expires = null)
    {

        if ($now == null) {
            $now = new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone));
        }
        if ($expires == null) {
            $expires = $now->modify('+3 hour');
        }
        $token = Yii::$app->jwt->getBuilder()
            ->issuedBy(Yii::$app->request->hostInfo)
            ->issuedAt($now) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expires) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $this->id) // Configures a new claim, called "uid"
            ->getToken(
                Yii::$app->jwt->getConfiguration()->signer(),
                Yii::$app->jwt->getConfiguration()->signingKey()
            );
        return (string) $token->toString();
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


    public static function tokenToId($token)
    {
        $claims = Yii::$app->jwt->parse($token)->claims();
        $uid = $claims->get('uid');
        return $uid;
    }
    /**
     * {@inheritdoc}
     * @param \Lcobucci\JWT\Token $token
     */
    public static function findByToken($token)
    {
        $claims = Yii::$app->jwt->parse($token)->claims();
        $uid = $claims->get('uid');
        $user = static::findIdentity($uid);
        // $user->token = $token;
        return $user;
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
        $this->password = $password;
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->one();

    }

    public function addRoles($roles)
    {
        $assignment = new Assignment($this->id);
        $assignment->assign($roles);
    }
    public function removeRoles($roles)
    {
        $assignment = new Assignment($this->id);
        $assignment->revoke($roles);
    }



    public static function create($username, $password)
    {
        $user = new User();
        $user->new_version = true;
        $user->username = $username;
        $user->setPassword(password: $password);
        $user->generateAuthKey();
        return $user;
    }

    //表名
    public static function tableName()
    {
        return '{{%user}}';
    }
    public $new_version = false;
    public $password;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [[/*'status',*/ 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', /*'verification_token', 'access_token',*/ 'nickname'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'required'],
            [['username', 'password_reset_token'], 'unique'],
            [['password'], 'string', 'min' => 6, 'max' => 20, 'message' => 'Password must be between 6 and 20 characters.'],
            ['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/i', 'message' => 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.'],
       
        ];
        if ($this->new_version) {
            $rules[] = ['username', 'email', 'message' => 'The email format is invalid.'];
        }

        return $rules;

    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',//id 保留
            'username' => 'Username',//用户名 保留
            'auth_key' => 'Auth Key',//授权 key 必须
            'password_hash' => 'Password Hash',//密码 保留
            'password_reset_token' => 'Password Reset Token',// 修改密码用的 token 考虑

            //  'status' => 'Status',//状态 可选
            'created_at' => 'Created At',//创建时间 保留
            'updated_at' => 'Updated At',//更新时间 保留
            //  'verification_token' => 'Verification Token',//不保留
            //  'access_token' => 'Access Token',// 保留
            //'wx_openid' => 'Wx Openid',//微信openid 下次取消
            'nickname' => 'Nickname',//昵称 保留
        ];
    }


}
