<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_event".
 *
 * @property int $id
 * @property int $verse_id
 * @property string|null $data
 *
 * @property Verse $verse
 */
class VerseEvent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_event';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id'], 'integer'],
            [['data'], 'string'],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
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
            'data' => 'Data',
        ];
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
     * @return VerseEventQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseEventQuery(get_called_class());
    }
}
