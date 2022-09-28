<?php

namespace backend\controllers;
use \common\models\EditorData;
class EditorReaderController extends \yii\web\Controller
{

	public function actionTest(){
		$this->layout = false;
		$jsons = EditorData::findAll(['project_id' => 57]);
		$datas = array();
		foreach($jsons as $key=>$val){
		
			$data = new \stdClass();
			$data->type = $val->type;
			$data->node_id = $val->node_id;
			$data->data = json_decode($val->data);
			array_push($datas, $data);
		}
	
		
		$reader = \Yii::$app->getModule('reader');

		$out = $reader->deconstruct($datas, ['Action']);
        echo json_encode($out);
	
	}
    public function actionIndex()
    {
		$this->layout = false;
		$jsons = EditorData::findAll(['project_id' => 57]);
		$datas = array();
		foreach($jsons as $key=>$val){
		
			$data = new \stdClass();
			$data->type = $val->type;
			$data->node_id = $val->node_id;
			$data->data = json_decode($val->data);
			array_push($datas, $data);
		}
	
		
		$reader = \Yii::$app->getModule('reader');

		$out = $reader->reader($datas);
        return $this->render('index',['json'=>$out]);
    }

	private function getEditorScripts($project){
	
	
	
	}



}
