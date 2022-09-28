<?php

namespace common\models;

use Yii;
use api\modules\v1\models\User;

/**
 * This is the model class for table "invitation".
 *
 * @property int $id
 * @property string $code
 * @property int $sender_id
 * @property int $recipient_id
 * @property int $used
 * @property string $auth_item_name
 *
 * @property AuthItem $authItemName
 * @property User $recipient
 * @property User $sender
 */
class Invitation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invitation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sender_id'], 'required'],
            [['sender_id', 'recipient_id', 'used'], 'integer'],
            [['code', 'auth_item_name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['auth_item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['auth_item_name' => 'name']],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['recipient_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'sender_id' => 'Sender ID',
            'recipient_id' => 'Recipient ID',
            'used' => 'Used',
            'auth_item_name' => 'Auth Item Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'auth_item_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipient()
    {
        return $this->hasOne(User::className(), ['id' => 'recipient_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {

        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }

    /**
     * {@inheritdoc}
     * @return InvitationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InvitationQuery(get_called_class());
    }
}
