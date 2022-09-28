<?php

namespace api\modules\v1\models;

use mdm\admin\components\Configs;
use Yii;

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
 * @property Feedback[] $feedbacks
 * @property Feedback[] $feedbacks0
 * @property File[] $files
 * @property Invitation[] $invitations
 * @property Invitation[] $invitations0
 * @property Like[] $likes
 * @property Message[] $messages
 * @property Message[] $messages0
 * @property Meta[] $metas
 * @property Meta[] $metas0
 * @property Order[] $orders
 * @property Reply[] $replies
 * @property Reply[] $replies0
 * @property Resource[] $resources
 * @property Resource[] $resources0
 * @property UserInfo[] $userInfos
 * @property Verse[] $verses
 * @property Verse[] $verses0
 * @property VerseCyber[] $verseCybers
 * @property VerseCyber[] $verseCybers0
 * @property VerseOpen[] $verseOpens
 * @property VerseShare[] $verseShares
 * @property Wx[] $wxes
 */
class Person extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'access_token', 'wx_openid', 'nickname'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'unique'],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key']);
        unset($fields['password_hash']);
        unset($fields['password_reset_token']);
        unset($fields['status']);
        unset($fields['verification_token']);
        unset($fields['access_token']);
        unset($fields['wx_openid']);
        unset($fields['updated_at']);

        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'verification_token' => 'Verification Token',
            'access_token' => 'Access Token',
            'wx_openid' => 'Wx Openid',
            'nickname' => 'Nickname',
        ];
    }
    public function extraFields()
    {
        return ['data', 'roles'];
    }
    public function getData()
    {

        $data = $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
        $info = $data->one();
        if (!$info) {
            $info = new UserInfo();
            $info->user_id = $this->id;
            $info->save();
        }
       
        
        return  $info->toArray([], ['avatar']);
    }
    public function getRoles()
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($this->id);
        $ret = [];
        foreach ($assignments as $key => $value) {
            $ret[] = $value->roleName;

        }
        return $ret;
    }
    
    /**
     * Gets query for [[Feedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbacks()
    {
        return $this->hasMany(Feedback::className(), ['repairer' => 'id']);
    }

    /**
     * Gets query for [[Feedbacks0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbacks0()
    {
        return $this->hasMany(Feedback::className(), ['reporter' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Invitations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvitations()
    {
        return $this->hasMany(Invitation::className(), ['recipient_id' => 'id']);
    }

    /**
     * Gets query for [[Invitations0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvitations0()
    {
        return $this->hasMany(Invitation::className(), ['sender_id' => 'id']);
    }

    /**
     * Gets query for [[Likes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLikes()
    {
        return $this->hasMany(Like::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Messages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Messages0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages0()
    {
        return $this->hasMany(Message::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMetas()
    {
        return $this->hasMany(Meta::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Metas0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMetas0()
    {
        return $this->hasMany(Meta::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Replies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Reply::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Replies0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies0()
    {
        return $this->hasMany(Reply::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[Resources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resource::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Resources0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResources0()
    {
        return $this->hasMany(Resource::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[UserInfos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserInfos()
    {
        return $this->hasMany(UserInfo::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Verses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerses()
    {
        return $this->hasMany(Verse::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Verses0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerses0()
    {
        return $this->hasMany(Verse::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[VerseCybers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseCybers()
    {
        return $this->hasMany(VerseCyber::className(), ['author_id' => 'id']);
    }

    /**
     * Gets query for [[VerseCybers0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseCybers0()
    {
        return $this->hasMany(VerseCyber::className(), ['updater_id' => 'id']);
    }

    /**
     * Gets query for [[VerseOpens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseOpens()
    {
        return $this->hasMany(VerseOpen::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[VerseShares]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseShares()
    {
        return $this->hasMany(VerseShare::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Wxes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWxes()
    {
        return $this->hasMany(Wx::className(), ['user_id' => 'id']);
    }
}
