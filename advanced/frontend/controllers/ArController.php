<?php

namespace frontend\controllers;
use Yii;

use common\models\Project;
use common\models\ProjectSearch;

use common\models\LoginForm;

class ArController extends \yii\web\Controller
{
	//public $layout = "ar";
	public $layout = "ar";

	public function actionGuest(){
	
        return $this->render('guest');
	}
    public function actionIndex()
    {
		if (Yii::$app->user->isGuest) {
			return $this->redirect(['guest', 'mode'=>Yii::$app->request->get('mode', null)]);
        };

		$projects = Project::find()->where(['user_id' => Yii::$app->user->id])->all();
        return $this->render('index', ['projects' => $projects]);
    }
	public function actionVisit(){
		
		$projects = Project::find()->where(['sharing' => 1])->all();
		return $this->render('index', ['projects' => $projects]);
	}
	public function actionLogout(){
        Yii::$app->user->logout();
		return $this->redirect(['index','mode'=>Yii::$app->request->get('mode', null)]);
	}

	public function actionLogin(){
		if (!Yii::$app->user->isGuest) {
			return $this->redirect(['index','mode'=>Yii::$app->request->get('mode', null)]);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {

			return $this->redirect(['index', 'mode'=>Yii::$app->request->get('mode', null)]);
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model, 'mode'=>Yii::$app->request->get('mode', null)
            ]);
        }
	}
	public function actionSignup(){
        return $this->render('signup');
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
        if (($model = Project::findOne($id)) !== null) {
			return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
