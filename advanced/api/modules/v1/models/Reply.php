<?php

namespace api\modules\v1\models;

use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "reply".
 *
 * @property int $id
 * @property int $message_id
 * @property string $body
 * @property int $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 *
 * @property User $author
 * @property Message $message
 * @property User $updater
 */
class Reply extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],

                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'author_id',
                'updatedByAttribute' => 'updater_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reply';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message_id', 'body'], 'required'],
            [['message_id', 'author_id', 'updater_id'], 'integer'],
            [['body', 'info'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['updater_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updater_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'body' => 'Body',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'info' => 'Info',
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = function ($model) {
            if ("object" == gettype($model->created_at)) {
                return date("Y-m-d H:i:s", time());
            }
            return $model->created_at;
        };

        $fields['updated_at'] = function ($model) {
            if ("object" == gettype($model->updated_at)) {
                return date("Y-m-d H:i:s", time());
            }
            return $model->updated_at;
        };

        $fields['author'] = function () {
            return $this->author;
        };
        return $fields;
    }
    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
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
     * Gets query for [[Updater]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * {@inheritdoc}
     * @return ReplyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReplyQuery(get_called_class());
    }
/*
public function getAuthor()
{
$author = $this->author;
$result = new \stdClass();
$result->id = $author->id;
$result->username = $author->username;
$result->nickname = $author->nickname;
$result->email = $author->email;

return $result;
}
 */
    public function afterSave($insert, $changedAttributes)
    {
        $message = $this->message;
        $message->updated_at = $this->updated_at;
        $message->save();
        return true;
    }
    public function extraFields()
    {
        return ['author' => function () {
            return $this->author;
        }, 'message'];
    }
}
