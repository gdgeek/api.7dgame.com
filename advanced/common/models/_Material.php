<?php

namespace common\models;
use api\modules\v1\models\User;

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
 * @property int $polygen_id
 * @property string $name
 * @property string $color
 * @property double $smoothness
 * @property int|null $alpha
 *
 * @property File $albedo0
 * @property File $emission0
 * @property File $metallic0
 * @property File $normal0
 * @property File $occlusion0
 * @property Polygen $polygen
 * @property User $user
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
            [['albedo', 'metallic', 'normal', 'occlusion', 'emission', 'user_id', 'polygen_id'], 'integer'],
            [['user_id', 'polygen_id'], 'required'],
            [['name'], 'string'],
            [['smoothness', 'alpha'], 'number'],
            [['color'], 'string', 'max' => 255],
            [['albedo'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['albedo' => 'id']],
            [['emission'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['emission' => 'id']],
            [['metallic'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['metallic' => 'id']],
            [['normal'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['normal' => 'id']],
            [['occlusion'], 'exist', 'skipOnError' => true, 'targetClass' => File::className(), 'targetAttribute' => ['occlusion' => 'id']],
            [['polygen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Polygen::className(), 'targetAttribute' => ['polygen_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'albedo' => Yii::t('app', 'Albedo'),
            'metallic' => Yii::t('app', 'Metallic'),
            'normal' => Yii::t('app', 'Normal'),
            'occlusion' => Yii::t('app', 'Occlusion'),
            'emission' => Yii::t('app', 'Emission'),
            'user_id' => Yii::t('app', 'User ID'),
            'polygen_id' => Yii::t('app', 'Polygen ID'),
            'name' => Yii::t('app', 'Name'),
            'color' => Yii::t('app', 'Color'),
            'smoothness' => Yii::t('app', 'Smoothness'),
            'alpha' => Yii::t('app', 'Alpha'),
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

    public function deleteFiles(){
        $albedo = $this->getAlbedo0()->one();
        if($albedo){
            $albedo->delete();
            $this->albedo = null;
        }
        $metallic = $this->getMetallic0()->one();
        if($metallic){
            $metallic->delete();
            $this->metallic = null;
        }
        $normal = $this->getNormal0()->one();
        if($normal){
            $normal->delete();
            $this->normal = null;
        }
        $occlusion = $this->getOcclusion0()->one();
        if($occlusion){
            $occlusion->delete();
            $this->occlusion = null;
        }
        $emission = $this->getEmission0()->one();
        if($emission){
            $emission->delete();
            $this->emission = null;
        }
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
    public function getPolygen()
    {
        return $this->hasOne(Polygen::className(), ['id' => 'polygen_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return MaterialQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MaterialQuery(get_called_class());
    }

    function fields(){
        $fields = parent::fields();
        unset($fields['albedo']);
        unset($fields['normal']);
        unset($fields['metallic']);
        unset($fields['occlusion']);
        unset($fields['emission']);
        unset($fields['user_id']);
        unset($fields['polygen_id']);
        unset($fields['name']);


       //  $fields['normal'] = 1;//$this->normal0;
        return $fields;
    }

    public function extraFields() 
    { 
        return [

                 'albedo_file'=>function(){return $this->albedo0;},
                 'normal_file'=>function(){return $this->normal0;},
                 'metallic_file'=>function(){return $this->metallic0;},
                 'occlusion_file'=>function(){return $this->occlusion0;},
                 'emission_file'=>function(){return $this->emission0;},
               
              ]; 
    } 
}
