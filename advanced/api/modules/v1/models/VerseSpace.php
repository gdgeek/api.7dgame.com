<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "verse_space".
 *
 * @property int $id
 * @property int $verse_id
 * @property int $space_id
 * @property string $created_at
 *
 * @property Verse $verse
 * @property Space $space
 */
class VerseSpace extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'verse_space';
    }

    public function rules()
    {
        return [
            [['verse_id', 'space_id'], 'required'],
            [['verse_id', 'space_id'], 'integer'],
            [['created_at'], 'safe'],
            [['verse_id'], 'unique'],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::class, 'targetAttribute' => ['verse_id' => 'id']],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::class, 'targetAttribute' => ['space_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'verse_id' => Yii::t('app', 'Verse ID'),
            'space_id' => Yii::t('app', 'Space ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    public function getVerse()
    {
        return $this->hasOne(Verse::class, ['id' => 'verse_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }
}
