<?php


namespace backend\controllers;

use Yii;
use common\models\Project;
use common\models\ProjectSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class IdeController extends \yii\web\Controller
{
    public function actionIndex()
    {

		$id =  \Yii::$app->request->get('project');
		$project = $this->findProject($id);

		return $this->render('index', ['project' => $project]);
    }

	public function actionSubmit(){
		$post = Yii::$app->request->post();
		if(!isset($post['project'])){
			return "no id";
		}
		$id = $post['project'];
		
		
        $project = $this->findProject($id);
		if($project){
			if($post['logic']){
				$project->logic = $post['logic'];
			}
		
			if($project->validate()){
				$project->save();
				return "good";
			}else{
				echo json_encode($model->getErrors());
			};

		}
	
	}
	protected function findProject($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }
        return;
    }

}
