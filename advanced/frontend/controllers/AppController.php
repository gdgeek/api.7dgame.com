<?php

namespace frontend\controllers;
use api\modules\v1\models\Resource;
use api\modules\v1\models\ResourceSearch;
use frontend\models\User;
use yii\helpers\Url;
use Yii;
class AppController extends \yii\web\Controller
{
    
	public $layout = '@frontend/views/layouts/app/theme.php';
    public function actionIndex()
    {

        echo "hello world!";
    }
    public function actionCreate()
    {

        if(!empty(Yii::$app->request->get('access_token'))){
            $user = User::findByAccessToken(Yii::$app->request->get('access_token'));

            if(isset($user)){
                Yii::$app->user->login($user,  0);
            }else{

                Yii::$app->user->logout();
            }
        }
        if(Yii::$app->user->isGuest){
            Yii::$app->user->setReturnUrl(Url::current(['access_token' => null]));  
            return $this->redirect(['entry/login']);
        }
        $accessToken = null;
        if(empty(Yii::$app->request->get('access_token'))){
            $accessToken =  Yii::$app->user->identity->access_token;
        }

        $searchModel = new ResourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where(['user_id' => Yii::$app->user->id]);
        return $this->render('create', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'accessToken' => $accessToken,
        ]);
    }

    public function actionCreateSecond($polygen_id){
       //echo $polygen_id;

         return $this->render('create-second');
    }
}
