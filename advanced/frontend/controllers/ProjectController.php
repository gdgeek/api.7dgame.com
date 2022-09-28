<?php

namespace frontend\controllers;
use common\components\editor\Reader;
use Yii;
use common\models\Project;
use common\models\ProjectSearch;

use \common\models\EditorData;
use yii\web\NotFoundHttpException;

class ProjectController extends \yii\web\Controller
{
    public function actionIndex()
    {
		$searchModel = new ProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	protected function is_empty($json){
			
		$obj = json_decode($json);

		if(isset($obj)){
			foreach ($obj as $key=>$value)
			{
				return false;
			}
		}else{
			return true;
		}
		
	
		return true;
	}
	 /**
     * Finds the Polygon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Polygon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($project = Project::findOne($id)) !== null) {
		
			//$configure = json_decode($project->configure);
			if($this->is_empty($project->configure)){
				$jsons = EditorData::findAll(['project_id' =>  $id]);
				$datas = array();

				foreach($jsons as $key=>$val){
					$data = new \stdClass();
					$data->type = $val->type;
					$data->node_id = $val->node_id;
					$data->data = json_decode($val->data);
					array_push($datas, $data);
				}
	
				$reader = new Reader();
				$project->configure = json_encode($reader->reader($datas));
				$project->save();
			}
			

		
			//echo json_encode($this->is_empty($project->configure));


			//echo "======================";
			return $project;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }


	/*public function actionLogic(){
	
	}*/
	public function actionFile(){

		$this->layout = false;
		$searchModel = new ProjectSearch();
		$filename = Yii::$app->request->get('filename');
		$type = Yii::$app->request->get('type');
		$id = Yii::$app->request->get('id');
		switch($filename){
			case "project.mrpp":
			
				$project = $this->findModel($id);
				return $this->render('project_mrpp', ['project' => $project]);

			break;


			case "logic.lua":
				return $this->render('logic_lua',['project' => $this->findModel($id)]);
			break;
			case "mession.json":
				return $this->render('mession_json',['id' => $id]);
			break;
			case "configure.json":
				

				$jsons = EditorData::findAll(['project_id' =>  $id]);
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
				return $this->render('configure',['json'=>$out]);



				//return $this->render('configure_json',['project' => $this->findModel($id)]);
			break;
			case "test.json":
				return $this->render('configure_json',['project' => $this->findModel($id)]);
			break;
			

		}

		return $this->render('file', [
            'searchModel' => $searchModel,
			'filename' => $filename,
			'type' => $type,
			'id' => $id,
        ]);
	}
}
