<?php

namespace api\modules\e1\models;

use api\modules\v1\models\MetaKnightQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
            [['verse_id', 'knight_id', 'user_id'], 'integer'],
            [['info'], 'string'],
            [['create_at'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
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
    public function getResourceIds()
    {
        if ($this->knight == null) {
            return [];
        }
        return $this->knight->getResourceIds();
    }
    public function fields()
    {
        $fields = parent::fields();

        return [
            'id',
            'data' => function ($model) {
                $knight = $this->knight;
                if (!$knight) {
                    return null;
                }
                return $this->knight->data;
            },
            'knight_id',
            'mesh' => function ($model) {
                $knight = $this->knight;
                if (!$knight) {
                    return null;
                }
                return $this->knight->mesh;
            },
            'info' => function ($model) {
                $knight = $this->knight;
                if (!$knight) {
                    return null;
                }
                return $this->knight->info;
            },
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
}