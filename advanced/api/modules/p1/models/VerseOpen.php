<?php

namespace api\modules\p1\models;

use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "verse_open".
 *
 * @property int $id
 * @property int $verse_id
 * @property int|null $user_id
 * @property int|null $message_id
 *
 * @property Message $message
 * @property User $user
 * @property Verse $verse
 */
class VerseOpen extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_open';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id', 'user_id', 'message_id'], 'integer'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['verse_id'], 'unique', 'targetAttribute' => ['verse_id']],
        ];
    }
    public function extraAuthor()
    {
        $author = $this->author;
        $result = new \stdClass();
        $result->id = $author->id;
        $result->username = $author->username;
        $result->nickname = $author->nickname;
        $result->email = $author->email;

        return $result;
    }

    public function extraVerse()
    {

        $verse = $this->verse;
        $result = $verse->attributes;
        $result['image'] = $verse->image;
        return $result;
    }
    public function extraFields()
    {
        return ['message',
            'verse' => function () {
                return $this->extraVerse();

            },
            'author' => function () {
                return $this->user;
            }];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verse_id' => 'Verse ID',
            'user_id' => 'User ID',
            'message_id' => 'Message ID',
        ];
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
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery|VerseQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }

    /**
     * {@inheritdoc}
     * @return VerseOpenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseOpenQuery(get_called_class());
    }
    public function afterDelete()
    {
        parent::afterDelete();
        $message = $this->message;
        if ($message) {
            $message->delete();
        }
    }

    public function beforeSave($insert)
    {
        $ret = parent::beforeSave($insert);
        if ($this->isNewRecord) {
            $tag = Tags::findOne(['name' => '展示']);
            if ($tag) {
                $mt = new MessageTags();
                $mt->message_id = $this->message_id;
                $mt->tag_id = $tag->id;
                $mt->save();
            }
        }
        return $ret;

    }
}
