<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_tags".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $tags_id
 *
 * @property Tags $tags
 * @property Verse $verse
 */
class VerseTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_tags';
    }

    public  function fields()
    {
        return [
            'tags_id'
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'tags_id'], 'required'],
            [['verse_id', 'tags_id'], 'integer'],
            [['tags_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tags::className(), 'targetAttribute' => ['tags_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['verse_id', 'tags_id'], 'unique', 'targetAttribute' => ['verse_id', 'tags_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verse_id' => 'Verse ID',
            'tags_id' => 'Tags ID',
        ];
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasOne(Tags::className(), ['id' => 'tags_id']);
    }

    /**
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }
}
