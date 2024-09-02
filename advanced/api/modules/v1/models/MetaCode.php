<?php

namespace api\modules\v1\models;


use api\modules\v1\components\Validator\JsonValidator;
use Yii;

/**
* This is the model class for table "meta_code".
*
* @property int $id
* @property string|null $blockly
* @property int $meta_id
* @property int|null $code_id
*
* @property Code $code
* @property Meta $meta
*/
class MetaCode extends \yii\db\ActiveRecord
{
    /**
    * {@inheritdoc}
    */
    public static function tableName()
    {
        return 'meta_code';
    }
    
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['blockly'], 'string'],
            [['meta_id'], 'required'],
            [['meta_id', 'code_id'], 'integer'],
            [['meta_id'], 'unique'],
            [['code_id'], 'unique'],
            [['code_id'], 'exist', 'skipOnError' => true, 'targetClass' => Code::className(), 'targetAttribute' => ['code_id' => 'id']],
            [['meta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Meta::className(), 'targetAttribute' => ['meta_id' => 'id']],
        ];
    }
    
    /**
    * {@inheritdoc}
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blockly' => 'Blockly',
            'meta_id' => 'Meta ID',
            'code_id' => 'Code ID',
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['meta_id']);
        unset($fields['code_id']);
        $fields['blockly'] = function () { 
            return JsonValidator::to_string($this->blockly);
        };
        
        return $fields;
    }
    /**
    * Gets query for [[Code]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getCode()
    {
        return $this->hasOne(Code::className(), ['id' => 'code_id']);
    }
    
    /**
    * Gets query for [[Meta]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['id' => 'meta_id']);
    }
}
