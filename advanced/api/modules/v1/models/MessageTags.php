<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "message_tags".
 *
 * @property int $id
 * @property int $message_id
 * @property int $tag_id
 *
 * @property Message $message
 * @property Tags $tag
 */
class MessageTags extends \yii\db\ActiveRecord
{
     function relations() {
        return array(
            'message'=>array( self::BELONGS_TO, 'Message', 'message_id' ),
            'tags'=>array( self::BELONGS_TO, 'Tags', 'tag_id' ),
        );
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message_id', 'tag_id'], 'required'],
            [['message_id', 'tag_id'], 'integer'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tags::className(), 'targetAttribute' => ['tag_id' => 'id']],
            [['message_id', 'tag_id'], 'unique', 'targetAttribute' => ['message_id', 'tag_id']],
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
            'tag_id' => 'Tag ID',
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
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery|TagsQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tags::className(), ['id' => 'tag_id']);
    }
    public function extraMessage(){
          
        $message = $this->message;
        $author = $message->extraAuthor();
        $result = $message->attributes;
        $result['author'] = $author;
        $result['messageTags'] = $message->messageTags;


        return $result;

    }
    public function extraFields() 
    { 
        return ['message' => function(){
            return $this->extraMessage();
        }]; 
    } 
    /**
     * {@inheritdoc}
     * @return MessageTagsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageTagsQuery(get_called_class());
    }
}
