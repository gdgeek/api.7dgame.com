<?php

namespace backend\controllers;
use common\models\AuthAssignment;
use common\models\AuthItem;
use Yii;
use common\models\Invitation;
use common\models\InvitationSearch;

use yii\web\NotFoundHttpException;

class HomeController extends \yii\web\Controller
{
    public function actionIndex()
    {

	
        return $this->render('index');
    }
	public function actionInvitation()
    {
        $searchModel = new InvitationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where(['sender_id' => Yii::$app->user->id]);

        $aa = AuthAssignment::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($aa->item_name == 'root') {

            $authItems = AuthItem::find()->where(['type' => 1])->andWhere(['<>', 'name', 'root'])->all();
        }elseif($aa->item_name == 'agent'){

            $authItems = AuthItem::find()->where(['type' => 1, 'name'=>'user'])->all();

        }
		
        return $this->render('invitation', [
            'authItems' => $authItems,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	public function actionAddInvitation(){

	
		$model = new Invitation();
		$model->sender_id = Yii::$app->user->id;
		$model->code = Yii::$app->user->id.'_'.time();
		$model->auth_item_name = Yii::$app->request->get('name');
        if ($model->save()) {
           $this->redirect(array('home/invitation'));
        }

	}
}
