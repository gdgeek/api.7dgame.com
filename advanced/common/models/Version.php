<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "version".
 *
 * @property int $id
 * @property int|null $version
 *
 * @property Url[] $urls
 */
class Version extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version'], 'integer'],
            [['version'], 'unique'],
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
        ];
    }

    /**
     * Gets query for [[Urls]].
     *
     * @return \yii\db\ActiveQuery|UrlQuery
     */
    public function getUrls()
    {
        return $this->hasMany(Url::className(), ['version' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return VersionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VersionQuery(get_called_class());
    }

    public function extraFields() 
    { 
        return ['urls']; 
    } 

}
