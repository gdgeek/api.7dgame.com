<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "url".
 *
 * @property int $id
 * @property int|null $version
 * @property string|null $key
 * @property string|null $value
 *
 * @property Version $version0
 */
class Url extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'url';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version'], 'integer'],
            [['key', 'value'], 'string', 'max' => 255],
            [['version'], 'exist', 'skipOnError' => true, 'targetClass' => Version::className(), 'targetAttribute' => ['version' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    /**
     * Gets query for [[Version0]].
     *
     * @return \yii\db\ActiveQuery|VersionQuery
     */
    public function getVersion0()
    {
        return $this->hasOne(Version::className(), ['id' => 'version']);
    }

    /**
     * {@inheritdoc}
     * @return UrlQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UrlQuery(get_called_class());
    }
}
