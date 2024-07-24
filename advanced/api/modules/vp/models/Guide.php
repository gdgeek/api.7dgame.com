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
    public function fields()
    {
        $fields = parent::fields();
      //  $fields['level'] = function () {return $this->level;};
        return $fields;
    }
    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery|VerseQuery
     */
    public function getLevel()
    {
        return $this->hasOne(Verse::className(), ['id' => 'level_id']);
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
