<?php

namespace api\modules\v1\models;

use api\modules\v1\models\MessageTags;
use api\modules\v1\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $body
 * @property int|null $author_id
 * @property int|null $updater_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 *
 * @property User $author
 * @property User $updater
 * @property MessageTags[] $messageTags
 * @property Reply[] $replies
 */
class Message extends \yii\db\ActiveRecord

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
        return 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['title', 'body'], 'required'],
            [['body', 'info'], 'string'],
            [['author_id', 'updater_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
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
            'title' => 'Title',
            'body' => 'Body',
            'author_id' => 'Author ID',
            'updater_id' => 'Updater ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'info' => 'Info',
        ];
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

    public function extraFields()
    {
        return [
            'author' => function () {
                return $this->extraAuthor();
            }, 
        
            'likes', 
            'likesCount' => function(){
                return $this->likesCount();
            },
            'like',
            'verseOpen',
            'replies', 
            'messageTags'
        ];
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
     * Gets query for [[MessageTags]].
     *
     * @return \yii\db\ActiveQuery|MessageTagsQuery
     */
    public function getMessageTags()
    {
        return $this->hasMany(MessageTags::className(), ['message_id' => 'id']);
    }

    /**
     * Gets query for [[Replies]].
     *
     * @return \yii\db\ActiveQuery|ReplyQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Reply::className(), ['message_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }
    public function likesCount(){
        $query = $this->getLikes();
        return $query->count();

    }
    public function getLike(){
        return $this->hasOne(Like::className(),['message_id' => 'id'])
        ->andWhere(['user_id' => \Yii::$app->user->identity->id]);
    }
    /**
     * Gets query for [[Likes]].
     *
     * @return \yii\db\ActiveQuery|LikeQuery
     */
    public function getLikes()
    {
        return $this->hasMany(Like::className(), ['message_id' => 'id']);
    }

    /**
     * Gets query for [[VerseOpens]].
     *
     * @return \yii\db\ActiveQuery|VerseOpenQuery
     */
    public function getVerseOpen()
    {
        return $this->hasOne(VerseOpen::className(), ['message_id' => 'id']);
    }
}
