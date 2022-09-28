<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property int $id
 * @property string $title
 * @property int $type
 * @property string $picture
 * @property string $video
 * @property string $text
 * @property string $blog
 * @property string $created_at
 *
 * @property ContentType $type0
 */
class Content extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['text'], 'string'],
            [['created_at'], 'safe'],
            [['title', 'picture', 'video', 'blog'], 'string', 'max' => 255],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::className(), 'targetAttribute' => ['type' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'type' => Yii::t('app', 'Type'),
            'picture' => Yii::t('app', 'Picture'),
            'video' => Yii::t('app', 'Video'),
            'text' => Yii::t('app', 'Text'),
            'blog' => Yii::t('app', 'Blog'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType0()
    {
        return $this->hasOne(ContentType::className(), ['id' => 'type']);
    }

    /**
     * {@inheritdoc}
     * @return ContentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentQuery(get_called_class());
    }
}
