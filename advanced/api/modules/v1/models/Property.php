<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property int $id
 * @property string $key
 * @property string|null $info
 *
 * @property VerseProperty[] $verseProperties
 */
class Property extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['info'], 'safe'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'key' => Yii::t('app', 'Key'),
            'info' => Yii::t('app', 'Info'),
        ];
    }

    /**
     * Gets query for [[VerseProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseProperties()
    {
        return $this->hasMany(VerseProperty::className(), ['property_id' => 'id']);
    }
}
