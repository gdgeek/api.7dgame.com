<?php

namespace api\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "meta_knight".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $meta_id
 * @property int $user_id
 * @property string|null $info
 * @property string|null $create_at
 * @property string|null $uuid
 * @property int|null $event_node_id
 *
 * @property EventNode $eventNode
 * @property Meta $meta
 * @property User $user
 * @property Verse $verse
 * @property string|null $uuid
 */
class MetaKnight extends \yii\db\ActiveRecord

{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meta_knight';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id'], 'required'],
            [['verse_id', 'meta_id', 'user_id', 'event_node_id'], 'integer'],
            [['info'], 'string'],
            [['create_at'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['event_node_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventNode::className(), 'targetAttribute' => ['event_node_id' => 'id']],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'meta_id' => 'Meta ID',
            'user_id' => 'User ID',
            'info' => 'Info',
            'create_at' => 'Create At',
            'uuid' => 'Uuid',
            'event_node_id' => 'Event Node ID',
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
    public function getResourceIds()
    {
        if ($this->meta == null) {
            return [];
        }
        return $this->meta->getResourceIds();
    }
    public function fields()
    {
        $fields = parent::fields();
        return [
            'id',
            'uuid',
            'data' => function ($model) {
                $meta = $this->meta;
                if (!$meta) {
                    return null;
                }
                return $this->meta->data;
            },
            'meta_id',

            'info',
            'event_node' => function ($model) {
                return $this->eventNode;
            },
        ];
    }
    /**
     * Gets query for [[Meta]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
     * @return MetaKnightQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MetaKnightQuery(get_called_class());
    }
    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->eventNode != null) {
            $this->eventNode->delete();
        }
    }
    public function beforeSave($insert)
    {
        $ret = parent::beforeSave($insert);
        if ($this->eventNode == null) {
            $node = new EventNode();
            $node->verse_id = $this->verse_id;
            $node->save();
            $this->event_node_id = $node->id;
        }
        return $ret;
    }
}
