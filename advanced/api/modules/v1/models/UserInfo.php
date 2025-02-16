<?php

namespace api\modules\v1\models;

use api\modules\v1\models\File;

use api\modules\v1\components\Validator\JsonValidator;
use Yii;

/**
 * This is the model class for table "user_info".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $info
 * @property int|null $avatar_id
 * @property int $gold
 * @property int $points
 *
 * @property File $avatar
 * @property User $user
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'avatar_id', 'gold', 'points'], 'integer'],
            [['info'], JsonValidator::class],
            [['avatar_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['avatar_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

 
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'info' => 'Info',
            'avatar_id' => 'Avatar ID',
            'gold' => 'Gold',
            'points' => 'Points',
        ];
    }

    public function extraFields()
    {
        return ['avatar'];
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['user_id']);
       
        return $fields;
    }

    /**
     * Gets query for [[Avatar]].
     *
     * @return \yii\db\ActiveQuery|FileQuery
     */
    public function getAvatar()
    {
        return $this->hasOne(File::className(), ['id' => 'avatar_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return UserInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserInfoQuery(get_called_class());
    }
}
