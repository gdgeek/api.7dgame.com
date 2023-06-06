<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "event_input".
 *
 * @property int $id
 * @property int $event_node_id
 * @property string|null $title
 * @property string|null $uuid
 *
 * @property EventNode $eventNode
 * @property EventLink[] $eventLinks
 */
class EventInput extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'event_input';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_node_id'], 'required'],
            [['event_node_id'], 'integer'],
            [['title', 'uuid'], 'string', 'max' => 255],
            [['event_node_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventNode::className(), 'targetAttribute' => ['event_node_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_node_id' => 'Event Node ID',
            'title' => 'Title',
            'uuid' => 'Uuid',
        ];
    }

    /**
     * Gets query for [[EventNode]].
     *
     * @return \yii\db\ActiveQuery|EventNodeQuery
     */
    public function getEventNode()
    {
        return $this->hasOne(EventNode::className(), ['id' => 'event_node_id']);
    }

    /**
     * Gets query for [[EventLinks]].
     *
     * @return \yii\db\ActiveQuery|EventLinkQuery
     */
    public function getEventLinks()
    {
        return $this->hasMany(EventLink::className(), ['event_input_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return EventInputQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EventInputQuery(get_called_class());
    }
}
