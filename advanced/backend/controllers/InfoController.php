<?php

namespace backend\controllers;

use Yii;
use common\models\Info;
use common\models\InfoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use common\models\Invitation;
use yii\db\Expression;

/**
 * InfoController implements the CRUD actions for Info model.
 */
class InfoController extends Controller
{

	public $layout = '@backend/views/layouts/site/main.php';
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Info models.
     * @return mixed
     */
    public function actionIndex()
    {

	 return $this->redirect(['info']);
	 /*
        $searchModel = new InfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);*/
    }
	public function actionNoInvitation(){
		
		
		return $this->render('none');
	}
	public function actionCreateInvitation(){
		$get = Yii::$app->request->get();
		//echo  $get['tel'];
		$info = \common\models\Info::findOne(['tel'=>$get['tel']]);
		//echo json_encode($info);
		if($info){
			//echo $info->invitation;
			if($info->invitation){
				return $this->render('find', [
					'invitation' => $info->invitation,
					]);
			}else{
				$aa = \common\models\AuthAssignment::findOne(['item_name'=>'root']);
				$invitation = new Invitation();
				$invitation->sender_id =$aa->user_id;
				$invitation->code = $aa->user_id.'_'.time();
				$invitation->auth_item_name = 'user';
				$info->invitation = $invitation->code;


				if($invitation->save() && $info->save()) 
				{
				
					return $this->render('succeed', [
					'invitation' => $invitation->code,
					]);
				}

				

			}
		}

		return $this->render('error',['text'=>'无法得到您的测试码']);
		
	}

	public function actionInfo() 
	{ 
		$model = new \common\models\Info(); 
		
		$is = \common\models\Invitation::find()->where(["date_format(create_at,'%Y-%m-%d')" =>new Expression("date_format(now(),'%Y-%m-%d')")])->all();
		if(count($is) >= 10){
			 return $this->redirect(['no-invitation','invitation'=>count($is)]);
		}
		if ($model->load(Yii::$app->request->post())) { 
			if ($model->validate() && $model->save()) { 
				
				 return $this->redirect(['create-invitation','tel'=>$model->tel]);
			} 
		} 

		return $this->render('Info', [ 
			'model' => $model, 
		]); 
	} 

    /**
     * Displays a single Info model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Info model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Info();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Info model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Info model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Info model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Info the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Info::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
