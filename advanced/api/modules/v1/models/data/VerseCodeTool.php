<?php

namespace api\modules\v1\models\data;
use api\modules\v1\models\Verse;
use api\modules\v1\models\Code;

use api\modules\v1\components\Validator\JsonValidator;
use yii\base\Model;


class VerseCodeTool extends Model
{
    private $verse;
    public $blockly;
    public $js;
    public $lua;
    public function __construct($id, array $config = [])
    {
        
        
        $this->verse = Verse::find()->where(['id' => $id])->one();
        
        if(!$this->verse){
            throw new \yii\web\NotFoundHttpException("Verse not found");
        }
        parent::__construct($config);
    }
    public function save()
    {
        $verseCode = $this->verse->verseCode;
        $verseCode->blockly = $this->blockly;
        if($this->lua || $this->js){
            $code = $verseCode->code;
            if(!$code){
                $code = new Code();
            }
            if($this->lua){
                $code->lua = $this->lua;
            }
            if($this->js){
                $code->js = $this->js;
            }
            if($code->validate()){
                $code->save();
                $verseCode->code_id = $code->id;
            }else{
                throw new \yii\web\ServerErrorHttpException(json_encode($code->errors));
            }
        }
        
        if($verseCode->validate()){
            $verseCode->save();
        }else{
            if($code)
            {
                $code->delete();
            }
            throw new \yii\web\ServerErrorHttpException(json_encode($verseCode->errors));
        }
        
        // echo json_encode($verseCode);
        /*
        if ($this->validate()) {
        $this->_user->nickname = $this->nickname;
        
        $info = $this->_user->userInfo;
        
        $info->info = $this->info;
        $info->avatar_id = $this->avatar_id;
        
        
        if ($this->_user->validate()) {
        $this->_user->save();
        return true;
        }
        return false;
        } else {
        return false;
        }*/
    }
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['blockly'], JsonValidator::class],
            // [['blockly'], 'required'],
            [['js','lua'], 'string'],
        ];
    }
    
}
