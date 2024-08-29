<?php

namespace api\modules\v1\models\data;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Code;

use yii\base\Exception;
use api\modules\v1\components\Validator\JsonValidator;
use yii\base\Model;


class MetaCodeTool extends Model
{
    private $meta;
    public $blockly;
    public $js;
    public $lua;
    public function __construct($id, array $config = [])
    {
        
        
        $this->meta = Meta::find()->where(['id' => $id])->one();
        
        if(!$this->meta){
            throw new \yii\web\NotFoundHttpException("Meta not found");
        }
        parent::__construct($config);
    }
    public function save()
    {
        $metaCode = $this->meta->metaCode;
        $metaCode->blockly = $this->blockly;
        if($this->lua || $this->js){
            $code = $metaCode->code;
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
                $metaCode->code_id = $code->id;
                $metaCode->save();
            }else{
                throw new \yii\web\ServerErrorHttpException(json_encode($code->errors));
            }
        }
        
        if($metaCode->validate()){
            $metaCode->save();
        }else{
            if($code)
            {
                $code->delete();
            }
            throw new \yii\web\ServerErrorHttpException(json_encode($metaCode->errors));
        }
        
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
