<?php
namespace api\modules\private\models;

use Yii;

/**
 * This is the model class for table "manager".
 *
 * @property int $id
 * @property int $verse_id
 * @property string $type
 * @property string|null $data
 *
 * @property Verse $verse
 */
class Manager extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'manager';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['verse_id', 'type'], 'required'],
            [['verse_id'], 'integer'],
            [['data'], 'safe'],
            [['type'], 'string', 'max' => 255],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
            [['verse_id', 'type'], 'unique', 'targetAttribute' => ['verse_id', 'type']],
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
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
        ];
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
