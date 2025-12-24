<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "version".
 *
 * @property int $id
 * @property string $name
 * @property int $number
 * @property string $created_at
 * @property string|null $info
 *
 * @property MetaVersion[] $metaVersions
 * @property VerseVersion[] $verseVersions
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
            [['name', 'number', 'created_at'], 'required'],
            [['number'], 'integer'],
            [['created_at', 'info'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'number' => Yii::t('app', 'Number'),
            'created_at' => Yii::t('app', 'Created At'),
            'info' => Yii::t('app', 'Info'),
        ];
    }

    /**
     * Gets query for [[MetaVersions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMetaVersions()
    {
        return $this->hasMany(MetaVersion::className(), ['version_id' => 'id']);
    }

    /**
     * Gets query for [[VerseVersions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVerseVersions()
    {
        return $this->hasMany(VerseVersion::className(), ['version_id' => 'id']);
    }
}
