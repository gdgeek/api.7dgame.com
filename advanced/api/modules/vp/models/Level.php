<?php

namespace api\modules\vp\models;

use Yii;

use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "vp_level".
 *
 * @property int $id
 * @property int $player_id
 * @property int $guide_id
 * @property float|null $record
 * @property int|null $score
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Guide $guide
 * @property Token $player
 */
class Level extends \yii\db\ActiveRecord
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
            ]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vp_level';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['player_id', 'guide_id'], 'required'],
            [['player_id', 'guide_id', 'score'], 'integer'],
            [['record'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['guide_id'], 'exist', 'skipOnError' => true, 'targetClass' => Guide::className(), 'targetAttribute' => ['guide_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Token::className(), 'targetAttribute' => ['player_id' => 'id']],
            [['guide_id', 'player_id'], 'unique', 'targetAttribute' => ['guide_id', 'player_id']],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'player_id' => 'Player ID',
            'guide_id' => 'Guide ID',
            'record' => 'Record',
            'score' => 'Score',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Guide]].
     *
     * @return \yii\db\ActiveQuery|GuideQuery
     */
    public function getGuide()
    {
        return $this->hasOne(Guide::className(), ['id' => 'guide_id']);
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery|TokenQuery
     */
    public function getPlayer()
    {
        return $this->hasOne(Token::className(), ['id' => 'player_id']);
    }

    /**
     * {@inheritdoc}
     * @return LevelQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LevelQuery(get_called_class());
    }
}
