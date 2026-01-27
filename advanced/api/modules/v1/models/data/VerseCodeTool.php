<?php

namespace api\modules\v1\models\data;

use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseCode;

use yii\base\Exception;
use yii\base\Model;


class VerseCodeTool extends Model
{

    private $_verse;




    public function getVerse()
    {
        return $this->_verse;
    }


    public $blockly;
    public $js;
    public $lua;
    public function __construct($verse_id, array $config = [])
    {
        $this->_verse = Verse::find()->where(['id' => $verse_id])->one();
        if (!$this->verse) {
            throw new \yii\web\NotFoundHttpException("Verse not found");
        }
        parent::__construct($config);
    }
    public function save()
    {



        $verseCode = $this->verse->verseCode;

        if ($verseCode === null) {
            $verseCode = new VerseCode();
            $verseCode->verse_id = $this->verse->id;
        }


        $verseCode->blockly = $this->blockly;

        $verseCode->lua = $this->lua;
        $verseCode->js = $this->js;


        if ($verseCode->validate()) {
            $verseCode->save();
        } else {

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
