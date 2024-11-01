<?php

namespace api\modules\a1\models;

use api\modules\v1\models\EventNode;
use api\modules\v1\models\Knight;
use api\modules\v1\models\User;
use api\modules\v1\components\Validator\JsonValidator;
use Yii;

/**
 * This is the model class for table "meta_knight".
 *
 * @property int $id
 * @property int $verse_id
 * @property int|null $knight_id
 * @property int $user_id
 * @property string|null $info
 * @property string|null $create_at
 * @property string|null $uuid
 *
 * @property Knight $knight
 * @property User $user
 * @property Verse $verse
 */
class MetaKnight extends \yii\db\ActiveRecord

{
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
            [['verse_id', 'user_id'], 'required'],
            [['verse_id', 'knight_id', 'user_id', 'event_node_id'], 'integer'],
            [['info'], JsonValidator::class],
            [['create_at'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['event_node_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventNode::className(), 'targetAttribute' => ['event_node_id' => 'id']],
            [['knight_id'], 'exist', 'skipOnError' => true, 'targetClass' => Knight::className(), 'targetAttribute' => ['knight_id' => 'id']],
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
            'knight_id' => 'Knight ID',
            'user_id' => 'User ID',
            'info' => 'Info',
            'create_at' => 'Create At',
            'uuid' => 'Uuid',
        ];
    }

    /**
     * Gets query for [[Knight]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getKnight()
    {
        return $this->hasOne(Knight::className(), ['id' => 'knight_id']);
    }
    /**
     * Gets query for [[EventNode]].
     * @return \yii\db\ActiveQuery|EventNodeQuery
     */
    public function getEventNode()
    {
        return $this->hasOne(EventNode::className(), ['id' => 'event_node_id']);
    }
    public function getResourceIds()
    {
        if ($this->knight == null) {
            return [];
        }
        return $this->knight->getResourceIds();
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
        return new \api\modules\v1\models\MetaKnightQuery(get_called_class());
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['verse_id']);
        unset($fields['knight_id']);
        unset($fields['user_id']);
        unset($fields['create_at']);
        unset($fields['event_node_id']);
        unset($fields['id']);
        $fields['inputs'] = function ($model) {
            $ret = [];
            foreach ($this->eventNode->eventInputs as $input) {
                $ret[] = ['uuid' => $input->uuid, 'title' => $input->title];
            }
            return $ret;
        };
        $fields['type'] = function ($model) {
            $knight = $this->knight;
            if (!$knight || $this->knight->type == null) {
                return 'sample';
            }
            return $this->knight->type;
        };
        $fields['script'] = function ($model) {
            return '';
        };
        $fields['data'] = function ($model) {
            $knight = $this->knight;
            if (!$knight) {
                return null;
            }
            return $this->knight->data;
        };
        return $fields;
    }
}
