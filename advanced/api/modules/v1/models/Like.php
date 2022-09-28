<?php

namespace api\modules\v1\models;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use yii\db\Expression;

use Yii;

/**
 * This is the model class for table "like".
 *
 * @property int $id
 * @property int $user_id
 * @property int $message_id
 *
 * @property Message $message
 * @property User $user
 */
class Like extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value' => new Expression('NOW()'),
            ]
        ];
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id','message_id'], 'required'],
            [['user_id', 'message_id'], 'integer'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['message_id', 'user_id'], 'unique', 'targetAttribute' => ['message_id', 'user_id']],
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
            'message_id' => 'Message ID',
        ];
    }

    public function extraFields()
    {
        return ['message'];
    }

    /**
     * Gets query for [[Message]].
     *
     * @return \yii\db\ActiveQuery|MessageQuery
     */
    public function getMessage()
    {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
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
     * @return LikeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LikeQuery(get_called_class());
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        $message = $this->message;
        $message->updated_at = new Expression('NOW()');
        $message->save();
        return true;
    }
}
