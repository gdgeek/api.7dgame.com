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
            
            [['order', 'level_id', 'map_id'], 'integer'],
            [['level_id', 'map_id'], 'required'],
            [['level_id'], 'unique'],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['level_id' => 'id']],
        ];
    }
    
    public function fields()
    {
        $fields = parent::fields();
        $fields['user_data'] = function () {
            $level= $this->level;
            if($level == null){
                return null;
            }
            return ["score" => $level->score, "record" => $level->record, "defined"=> true];
        };
        unset($fields['order']);
        unset($fields['map_id']);
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
        return $model;
        
    }
    public function getVerse()
    {
        $verse = $this->hasOne(Verse::className(), ['id' => 'level_id']);
        return $verse->toArray([],['id','metas','name','description','uuid','data','code','resources']);
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
