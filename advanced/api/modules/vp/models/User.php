<?php

namespace api\modules\vp\models;

use yii\db\Expression;

use mdm\admin\components\Configs;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
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
 * @property int $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $verification_token
 * @property string|null $access_token
 * @property string|null $wx_openid
 * @property string|null $nickname
 *
 * @property AppleId[] $apples //苹果Id
 * @property Cyber[] $cybers //代码
 * @property File[] $files //文件
 * @property Invitation[] $invitations //邀请 全都要删除
 * @property Knight[] $knights0 //骑士 全都要删除
 * @property Like[] $likes //点赞
 * @property Message[] $messages //消息全都要删除
 * @property Meta[] $metas //元数据
 * @property MetaKnight[] $metaKnights //骑士全都删除
 * @property Order[] $orders //订单
 * @property Reply[] $replies //回复全都要删除
 * @property Resource[] $resources //资源
 * @property Space[] $spaces //空间
 * @property UserInfo[] $userInfos //用户信息
 * @property Verse[] $verses
 * @property VerseOpen[] $verseOpens //开放宇宙
 * @property VerseShare[] $verseShares //共享宇宙
 * @property Token[] $vpTokens 游戏 token
 * @property Wx[] $wxes //微信登录用
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{


    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $claims = \Yii::$app->jwt->parse($token)->claims();
        $uid = $claims->get('uid');
        $user = static::findIdentity($uid );
        $user->token = $token;
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
            'id', 'nickname', 'email',
            'username',
        ];
    }

    //生成token
    public function generateAccessToken()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone));
       
        $token = \Yii::$app->jwt->getBuilder()
            ->issuedBy(\Yii::$app->request->hostInfo)
            ->issuedAt($now) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+3 hour')) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $this->id) // Configures a new claim, called "uid"
            ->getToken(
                \Yii::$app->jwt->getConfiguration()->signer(),
                \Yii::$app->jwt->getConfiguration()->signingKey()
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

    
    /**
     * {@inheritdoc}
     * @param \Lcobucci\JWT\Token $token
     */
    public static function findByToken($token, $type = null)
    {
        $claims = \Yii::$app->jwt->parse($token)->claims();
        $uid = $claims->get('uid');
        $user = static::findIdentity($uid);
        $user->token = $token;
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
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function getRoles()
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($this->id);
        return array_values(array_map(function ($value) {return $value->roleName;}, $assignments));
    }


    public static function create($username, $password){
        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->generateAuthKey();
        return $user;
    }

     //表名
     public static function tableName()
     {
         return '{{%user}}';
     }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status','created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'access_token', 'wx_openid', 'nickname'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username', 'email'], 'required'],
            [['email'], 'unique'],
            ['email', 'email', 'message' => 'The email format is invalid.'],
            [['username'], 'unique'],    
            // 验证用户名的长度在5到30个字符之间
            ['username', 'string', 'min' => 5, 'max' => 256],
            // 验证用户名的规则
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_@.-]+$/', 'message' => 'Username can only contain letters, numbers, underscores, hyphens, @, and .'],
            [['password_reset_token'], 'unique'],
        ];
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
            'email' => 'Email',//邮箱 保留
            'status' => 'Status',//状态 可选
            'created_at' => 'Created At',//创建时间 保留
            'updated_at' => 'Updated At',//更新时间 保留
            'verification_token' => 'Verification Token',//不保留
            'access_token' => 'Access Token',// 不保留
            'wx_openid' => 'Wx Openid',//微信openid 下次取消
            'nickname' => 'Nickname',//昵称 保留
        ];
    }

    /**
     * Gets query for [[Apples]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppleId()//获取苹果id
    {
        return $this->hasOne(AppleId::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[VpToken]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVpToken()//获取游戏token
    {
        return $this->hasOne(Token::className(), ['user_id' => 'id']);
    }

}
