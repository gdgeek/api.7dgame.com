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

        $fields['avatar'] = function () {return $this->avatar;};
        $fields['roles'] = function () {return $this->roles;};
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

    public function getAvatar()
    {

        $info = $this->userInfo;
        if ($info && $info->avatar) {
            return $info->avatar;
        }

        return null;
    }
    public function getRoles()
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($this->id);
        return array_values(array_map(function ($value) {return $value->roleName;}, $assignments));
    }

    /**
     * Gets query for [[UserInfos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'id']);
    }

}
