<?php

namespace api\modules\vp\models;
use api\modules\a1\models\Verse;
use Yii;

/**
 * This is the model class for table "vp_guide".
 *
 * @property int $id
 * @property int|null $order
 * @property int $level_id
 *
 * @property Verse $level
 */
class Guide extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vp_guide';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
            [['order', 'level_id'], 'integer'],
            [['level_id'], 'required'],
            [['level_id'], 'unique'],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['level_id' => 'id']],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['record'] = function () {return $this->level;};
        unset($fields['order']);
       // $fields['playerId'] = function() {return \Yii::$app->player->token->id;};
        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order' => 'Order',
            'level_id' => 'Level ID',
        ];
    }
    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery|VerseQuery
     */
    public function getLevel()
    {
        $player_id = \Yii::$app->player->token->id;
        $model = Level::find()->where(['player_id' => $player_id, 'guide_id' => $this->id])->one();
        if($model == null){
          return null;
        }
        return ["score" =>$model->score, "record" => $model->record];
    }

    /**
     * {@inheritdoc}
     * @return GuideQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GuideQuery(get_called_class());
    }
}
