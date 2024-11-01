<?php

namespace backend\controllers;

class SetupController extends \yii\web\Controller

{
    private function mainTemplate()
    {
        /*
    $db = \backend\models\ScriptData::find()->where(['key'=>'main'])->one();
    if($db){
    $db = new \backend\models\ScriptData();
    $db->key = 'main';
    }
    $data->
    if (!$db->save()) {
    echo json_encode($db->getErrors());
    }*/

    }
    public function actionIndex()
    {

        $template = \Yii::$app->getModule('template');
        //echo 123;
        $ret = $template->getTemplate("main", 1);
        echo json_encode($ret);
        //$
        $script = "";
        $context = new \stdClass();
        $db = new \backend\models\ScriptData();
        $db->context = json_encode($context);
        $db->key = 'test';
        if (!$db->save()) {
            echo json_encode($db->getErrors());
        }
        return $this->render('index');
    }

}
