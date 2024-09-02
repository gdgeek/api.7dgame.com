<?php

namespace api\modules\v1\models;

use api\modules\v1\components\Validator\JsonValidator;
use Yii;

/**
* This is the model class for table "verse_code".
*
* @property int $id
* @property string|null $blockly
* @property int $verse_id
* @property int|null $code_id
*
* @property Code $code
* @property Verse $verse
*/
class VerseCode extends \yii\db\ActiveRecord
{
    /**
    * {@inheritdoc}
    */
    public static function tableName()
    {
        return 'verse_code';
    }
    
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['blockly'], 'string'],
            [['verse_id'], 'required'],
            [['verse_id', 'code_id'], 'integer'],
            [['verse_id'], 'unique'],
            [['code_id'], 'unique'],
            [['code_id'], 'exist', 'skipOnError' => true, 'targetClass' => Code::className(), 'targetAttribute' => ['code_id' => 'id']],
            [['verse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Verse::className(), 'targetAttribute' => ['verse_id' => 'id']],
        ];
    }
    
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        unset($fields['verse_id']);
        unset($fields['code_id']);
        $fields['blockly'] = function () { 
            return JsonValidator::to_string($this->blockly);
        };
        
        return $fields;
    }
    /**
    * {@inheritdoc}
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blockly' => 'Blockly',
            'verse_id' => 'Verse ID',
            'code_id' => 'Code ID',
        ];
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
    * Gets query for [[Verse]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getVerse()
    {
        return $this->hasOne(Verse::className(), ['id' => 'verse_id']);
    }
}
