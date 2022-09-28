<?php

namespace common\models;
use api\modules\v1\models\User;

use Yii;

/**
 * This is the model class for table "maker".
 *
 * @property int $id
 * @property int $user_id
 * @property int $polygen_id
 * @property string|null $data
 * @property int|null $programme_id
 *
 * @property Polygen $polygen
 * @property Programme $programme
 * @property User $user
 */
class Maker extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'maker';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'polygen_id'], 'required'],
            [['user_id', 'polygen_id', 'programme_id'], 'integer'],
            [['data'], 'string'],
            [['polygen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Polygen::className(), 'targetAttribute' => ['polygen_id' => 'id']],
            [['programme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Programme::className(), 'targetAttribute' => ['programme_id' => 'id']],
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
            'user_id' => Yii::t('app', 'User ID'),
            'polygen_id' => Yii::t('app', 'Polygen ID'),
            'data' => Yii::t('app', 'Data'),
            'programme_id' => Yii::t('app', 'Programme ID'),
        ];
    }

    /**
     * Gets query for [[Polygen]].
     *
     * @return \yii\db\ActiveQuery|PolygenQuery
     */
    public function getPolygen()
    {
        return $this->hasOne(Polygen::className(), ['id' => 'polygen_id']);
    }

    
   
    /**
     * Gets query for [[Programme]].
     *
     * @return \yii\db\ActiveQuery|ProgrammeQuery
     */
    public function getProgramme()
    {
        return $this->hasOne(Programme::className(), ['id' => 'programme_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public function extraPolygen(){
        $polygen = $this->polygen;
        $ret = new \stdClass();
        $ret->name = $polygen->name;
        $ret->id = $polygen->id;
        $ret->type =$polygen->type;
        $ret->file = $polygen->file;
        /*$ret->materials = array_map(function($item){
            $out =new \stdClass();
            $out->material_id = $item->material_id; 
            $out->key = $item->key; 
            return $out;

        }, $polygen->polygenMaterials);*/
        return $ret;
    }
    /**
     * {@inheritdoc}
     * @return MakerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MakerQuery(get_called_class());
    }
    function fields(){
        $fields = parent::fields();
        unset($fields['programme_id']);
        unset($fields['data']);

        return $fields;
    }
    public function extraFields() 
    { 
        return [
                 'programme',
                 'polygen'=>function(){
                     return $this->extraPolygen();
                 },
                 'data'=>function(){
                        return $this->data;
                 }
              ]; 
    } 
}
