<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "material".
 *
 * @property int $id
 * @property int $albedo
 * @property int $metallic
 * @property int $normal
 * @property int $occlusion
 * @property int $emission
 * @property int $user_id
 *
 * @property File $albedo0
 * @property File $emission0
 * @property File $metallic0
 * @property File $normal0
 * @property File $occlusion0
 * @property User $user
 * @property Polygen[] $polygens
 */
class Material extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'material';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['albedo', 'metallic', 'normal', 'occlusion', 'emission', 'user_id'], 'integer'],
            [['user_id'], 'required'],
            [['albedo'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['albedo' => 'id']],
            [['emission'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['emission' => 'id']],
            [['metallic'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['metallic' => 'id']],
            [['normal'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['normal' => 'id']],
            [['occlusion'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['occlusion' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'albedo' => 'Albedo',
            'metallic' => 'Metallic',
            'normal' => 'Normal',
            'occlusion' => 'Occlusion',
            'emission' => 'Emission',
            'user_id' => 'User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlbedo0()
    {
        return $this->hasOne(File::className(), ['id' => 'albedo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmission0()
    {
        return $this->hasOne(File::className(), ['id' => 'emission']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMetallic0()
    {
        return $this->hasOne(File::className(), ['id' => 'metallic']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNormal0()
    {
        return $this->hasOne(File::className(), ['id' => 'normal']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOcclusion0()
    {
        return $this->hasOne(File::className(), ['id' => 'occlusion']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPolygens()
    {
        return $this->hasMany(Polygen::className(), ['material_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return MaterialQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MaterialQuery(get_called_class());
    }
}
