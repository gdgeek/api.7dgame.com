<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_knight".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $knight_id
 *
 * @property Knight $knight
 * @property Verse $verse
 */
class VerseKnight extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_knight';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'knight_id'], 'required'],
            [['verse_id', 'knight_id'], 'integer'],
            [['knight_id'], 'exist', 'skipOnError' => true, 'targetClass' => Knight::className(), 'targetAttribute' => ['knight_id' => 'id']],
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
            'knight_id' => 'Knight ID',
        ];
    }

    /**
     * Gets query for [[Knight]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getKnight()
    {
        return $this->hasOne(Knight::className(), ['id' => 'knight_id']);
    }
    public function extraFields()
    {
        return [
            'image',
            'verseKnights',
            'author',
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
     * @return VerseKnightQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseKnightQuery(get_called_class());
    }
}
