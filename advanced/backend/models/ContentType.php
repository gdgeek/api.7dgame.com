<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content_type".
 *
 * @property int $id
 * @property string $type
 *
 * @property Content[] $contents
 */
class ContentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['type' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ContentTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ContentTypeQuery(get_called_class());
    }
}
