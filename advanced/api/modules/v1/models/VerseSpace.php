<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_space".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $space_id
 *
 * @property Space $space
 * @property Verse $verse
 */
class VerseSpace extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verse_space';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'space_id'], 'required'],
            [['verse_id', 'space_id'], 'integer'],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::className(), 'targetAttribute' => ['space_id' => 'id']],
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
            'space_id' => 'Space ID',
        ];
    }

    /**
     * Gets query for [[Space]].
     *
     * @return \yii\db\ActiveQuery|SpaceQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::className(), ['id' => 'space_id']);
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
     * @return VerseSpaceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VerseSpaceQuery(get_called_class());
    }
}
