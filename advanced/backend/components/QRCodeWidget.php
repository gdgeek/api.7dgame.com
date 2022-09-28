<?php
namespace backend\components;

use yii\base\Widget;
use yii\helpers\Html;

class QRCodeWidget extends Widget
{
    public $message;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {

        return $this->render('qrcode_view', ['local'=>\Yii::$app->params['information']['local']]);
       
    }
}