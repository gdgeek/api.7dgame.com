<?php

namespace backend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Project;
use app\models\Content;
use app\models\ContentSearch;
use app\models\ContentType;
use common\models\ProjectSearch;



class ListController extends \yii\web\Controller
{
	public function actionIndex()
    {
		$projects = Project::find()->where(['sharing' => 1])->all();
		return $this->render('projects', ['projects' => $projects]);
    }

	public function actionQrcode($id){
		return $this->renderpartial('qrcode', ['id'=>$id]);
	}

}
