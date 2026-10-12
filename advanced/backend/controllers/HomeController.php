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
        if (\api\modules\v1\services\IndependentIdentity::enabled()) {
            $data = \common\components\IdentityLegacyAdmin::call('invitation-list');
            $rows = array_map(static fn($row) => new \common\models\IdentityInvitationView($row), $data['invitations']);
            $searchModel = new \yii\base\DynamicModel(['code', 'used', 'auth_item_name', 'recipient_id']);
            $searchModel->addRule(['code', 'used', 'auth_item_name', 'recipient_id'], 'safe');
            if ($searchModel->load(Yii::$app->request->queryParams, 'InvitationSearch')) {
                $filters = array_filter($searchModel->attributes, static fn($value) => $value !== null && $value !== '');
                $rows = array_values(array_filter($rows, static function ($row) use ($filters) {
                    foreach ($filters as $field => $value) {
                        if (!str_contains((string)$row->$field, (string)$value)) return false;
                    }
                    return true;
                }));
            }
            return $this->render('invitation', [
                'authItems' => array_map(static fn($role) => (object)$role, $data['roles']), 'searchModel' => $searchModel,
                'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $rows, 'key' => 'id', 'pagination' => ['pageSize' => 20]]),
            ]);
        }
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

		if (\api\modules\v1\services\IndependentIdentity::enabled()) {
			\common\components\IdentityLegacyAdmin::call('invitation-create', ['auth_item_name' => Yii::$app->request->get('name')]);
			return $this->redirect(['home/invitation']);
		}
	
		$model = new Invitation();
		$model->sender_id = Yii::$app->user->id;
		$model->code = Yii::$app->user->id.'_'.time();
		$model->auth_item_name = Yii::$app->request->get('name');
        if ($model->save()) {
           $this->redirect(array('home/invitation'));
        }

	}
}
