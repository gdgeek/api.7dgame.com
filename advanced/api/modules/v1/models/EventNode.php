<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "event_node".
 *
 * @property int $id
 * @property int $verse_id
 *
 * @property EventInput[] $eventInputs
 * @property Verse $verse
 * @property EventOutput[] $eventOutputs
 * @property Meta[] $metas
 */
class EventNode extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_node';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id'], 'integer'],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        unset($fields['verse_id']);
        $fields['inputs'] = function () {return $this->eventInputs;};
        $fields['outputs'] = function () {return $this->eventOutputs;};
        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'verse_id' => 'Verse ID',
        ];
    }

    /**
     * Gets query for [[EventInputs]].
     *
     * @return \yii\db\ActiveQuery|EventInputQuery
     */
    public function getEventInputs()
    {
        return $this->hasMany(EventInput::className(), ['event_node_id' => 'id']);
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
     * Gets query for [[Metas]].
     *
     * @return \yii\db\ActiveQuery|MetaQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['event_node_id' => 'id']);
    }

    /**
     * Gets query for [[EventOutputs]].
     *
     * @return \yii\db\ActiveQuery|EventOutputQuery
     */
    public function getEventOutputs()
    {
        return $this->hasMany(EventOutput::className(), ['event_node_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return EventNodeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventNodeQuery(get_called_class());
    }
}
