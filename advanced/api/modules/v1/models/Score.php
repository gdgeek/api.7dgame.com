<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "score".
 *
 * @property int $id
 * @property int $verse_id
 * @property string $player_id
 * @property int|null $score
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Verse $verse
 */
class Score extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'score';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'player_id'], 'required'],
            [['verse_id', 'score'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['player_id'], 'string', 'max' => 255],
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
            'player_id' => 'Player ID',
            'score' => 'Score',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * @return ScoreQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ScoreQuery(get_called_class());
    }
}
