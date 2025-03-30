<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "snapshot".
 *
 * @property int $id
 * @property int|null $verse_id
 * @property string $snapshot
 * @property string|null $created_at
 * @property int|null $author_id
 * @property int|null $created_by
 * @property string|null $type
 *
 * @property User $author
 * @property User $createdBy
 * @property Verse $verse
 */
class Snapshot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'snapshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'author_id', 'created_by'], 'integer'],
            [['snapshot'], 'required'],
            [['snapshot', 'created_at'], 'safe'],
            [['type'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'verse_id' => Yii::t('app', 'Verse ID'),
            'snapshot' => Yii::t('app', 'Snapshot'),
            'created_at' => Yii::t('app', 'Created At'),
            'author_id' => Yii::t('app', 'Author ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Gets query for [[Verse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }
}
