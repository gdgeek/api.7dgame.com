<?php

namespace api\modules\v1\models\data;
use api\modules\v1\models\Verse;
use api\modules\v1\models\Code;

use yii\base\Exception;
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
        $code = $verseCode->code;
        if(!$code){
            $code = new Code();
        }
        $code->lua = $this->lua;
        $code->js = $this->js;
        if($code->validate()){
            $code->save();
            if(!$verseCode->code){
                $verseCode->code_id = $code->id;
                if($verseCode->validate()){
                    $verseCode->save();
                }else{
                    $code->delete();
                    throw new \yii\web\ServerErrorHttpException(json_encode($metaCode->errors));
                }
            }
            
        }else{
            throw new \yii\web\ServerErrorHttpException(json_encode($code->errors));
        }
    }
    /**
    * {@inheritdoc}
    */
    public function rules()
    {
        return [
            [['blockly'], 'string'],
            // [['blockly'], 'required'],
            [['js','lua'], 'string'],
        ];
    }
    
}
