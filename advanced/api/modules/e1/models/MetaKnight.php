<?php

namespace api\modules\e1\models;

use api\modules\v1\models\MetaKnightQuery;
use Yii;

/**
 * This is the model class for table "meta_knight".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $knight_id
 * @property int $user_id
 * @property string|null $info
 * @property string|null $create_at
 *
 * @property Knight $knight
 * @property User $user
 * @property Verse $verse
 * @property string|null $uuid
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
            [['verse_id', 'meta_id', 'user_id'], 'integer'],
            [['info'], 'string'],
            [['create_at'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Knight::className(), 'targetAttribute' => ['knight_id' => 'id']],
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
        ];
    }

    /**
     * Gets query for [[Knight]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Knight::className(), ['id' => 'meta_id']);
    }

    public function getResourceIds()
    {
        if ($this->meta == null) {
            return [];
        }
        return $this->meta->getResourceIds();
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
        unset($fields['meta_id']);
        unset($fields['user_id']);
        unset($fields['create_at']);
        unset($fields['id']);
        $fields['meta'] = function ($model) {
            return $this->meta;
        };
        $fields['type'] = function ($model) {
            $meta = $this->meta;
            if (!$meta || $this->meta->type == null) {
                return 'sample';
            }
            return $this->meta->type;
        };
        $fields['script'] = function ($model) {
            return '';
        };
        $fields['data'] = function ($model) {
            $meta = $this->meta;
            if (!$meta) {
                return null;
            }
            return $this->meta->data;
        };
        return $fields;
    }
}
