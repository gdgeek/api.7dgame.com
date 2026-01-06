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
    public function __construct($verse_id, array $config = [])
    {
        $this->verse = Verse::find()->where(['id' => $verse_id])->one();
        if (!$this->verse) {
            throw new \yii\web\NotFoundHttpException("Verse not found");
        }
        parent::__construct($config);
    }
    public function save()
    {
        $verseCode = $this->verse->verseCode;
        $verseCode->blockly = $this->blockly;
        $code = $verseCode->code;
        if (!$code) {
            $code = new Code();
        }
        $verseCode->lua = $this->lua;
        $verseCode->js = $this->js;
        $code->lua = $this->lua;
        $code->js = $this->js;
        if ($code->validate()) {
            $code->save();
            if (!$verseCode->code) {
                $verseCode->code_id = $code->id;

            }

        } else {
            throw new \yii\web\ServerErrorHttpException(json_encode($code->errors));
        }
        if ($verseCode->validate()) {
            $verseCode->save();
        } else {
            $code->delete();
            throw new \yii\web\ServerErrorHttpException(json_encode($verseCode->errors));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blockly'], 'string'],
            [['js', 'lua'], 'string'],
        ];
    }

}
