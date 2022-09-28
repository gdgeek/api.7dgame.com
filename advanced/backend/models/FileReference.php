<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "file_reference".
 *
 * @property int $id
 * @property int $file_id
 * @property string $type
 *
 * @property File $file
 * @property Material[] $materials
 * @property Material[] $materials0
 * @property Material[] $materials1
 * @property Material[] $materials2
 * @property Material[] $materials3
 */
class FileReference extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_reference';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id'], 'required'],
            [['file_id'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['file_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials()
    {
        return $this->hasMany(Material::className(), ['albedo' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials0()
    {
        return $this->hasMany(Material::className(), ['emission' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials1()
    {
        return $this->hasMany(Material::className(), ['metallic' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials2()
    {
        return $this->hasMany(Material::className(), ['normal' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaterials3()
    {
        return $this->hasMany(Material::className(), ['occlusion' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return FileReferenceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FileReferenceQuery(get_called_class());
    }
}
