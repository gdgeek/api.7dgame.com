<?php

namespace api\modules\a1\models;

use Yii;

/**
 * This is the model class for table "vp_score".
 *
 * @property int $id
 * @property int $verse_id
 * @property string|null $player_id
 * @property int|null $score
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Verse $verse
 */
class VpScore extends \yii\db\ActiveRecord
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
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vp_score';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id', 'score'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['player_id'], 'string', 'max' => 255],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['player_id', 'verse_id'], 'unique', 'targetAttribute' => ['player_id', 'verse_id']],
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
     * @return VpScoreQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VpScoreQuery(get_called_class());
    }
}
