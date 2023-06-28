<?php

namespace api\modules\a1\models;

use api\modules\v1\models\EventInput;
use api\modules\v1\models\EventLinkQuery;
use api\modules\v1\models\EventOutput;
use Yii;

/**
 * This is the model class for table "event_link".
 *
 * @property int $id
 * @property int $event_input_id
 * @property int $event_output_id
 * @property int $verse_id
 *
 * @property EventInput $eventInput
 * @property EventOutput $eventOutput
 * @property Verse $verse
 */
class EventLink extends \yii\db\ActiveRecord

{
    public function fields()
    {
        $fields = [];
        $fields['input'] = function ($model) {
            return $this->eventInput->uuid;
        };
        $fields['output'] = function ($model) {
            return $this->eventOutput->uuid;
        };
        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_link';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_input_id', 'event_output_id', 'verse_id'], 'required'],
            [['event_input_id', 'event_output_id', 'verse_id'], 'integer'],
            [['event_input_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventInput::className(), 'targetAttribute' => ['event_input_id' => 'id']],
            [['event_output_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventOutput::className(), 'targetAttribute' => ['event_output_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['event_input_id', 'event_output_id'], 'unique', 'targetAttribute' => ['event_input_id', 'event_output_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_input_id' => 'Event Input ID',
            'event_output_id' => 'Event Output ID',
            'verse_id' => 'Verse ID',
        ];
    }

    /**
     * Gets query for [[EventInput]].
     *
     * @return \yii\db\ActiveQuery|EventInputQuery
     */
    public function getEventInput()
    {
        return $this->hasOne(EventInput::className(), ['id' => 'event_input_id']);
    }

    /**
     * Gets query for [[EventOutput]].
     *
     * @return \yii\db\ActiveQuery|EventOutputQuery
     */
    public function getEventOutput()
    {
        return $this->hasOne(EventOutput::className(), ['id' => 'event_output_id']);
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
     * @return EventLinkQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventLinkQuery(get_called_class());
    }
}
