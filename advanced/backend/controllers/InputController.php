<?php

namespace backend\controllers;

use Yii;
use yii\helpers\HtmlPurifier;
use common\models\Project;
use backend\models\InputForm;
class InputController extends \yii\web\Controller
{
    public function actionIndex()
    {

	
		$get = Yii::$app->request->get();
		if(!isset($get['project'])){
			return "no id";
		}
		$id = $get['project'];
		$project = Project::findOne(['id'=>$id]);


		$model = new InputForm();

        if ($model->load(Yii::$app->request->post())) {
			$project->introduce = strip_tags($model->text);
			$project->save();
			$this->redirect(array('project/index'));
        }
		$model->text = $project->introduce;
        return $this->render('index', ['model' => $model]);
    }

}
