<?php
namespace api\controllers;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use yii\data\ActiveDataProvider;

use yii\filters\auth\CompositeAuth;

use sizeg\jwt\JwtHttpBearerAuth;

use common\models\Project;
use common\models\Programme;

class FileController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\File';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
    
        return $behaviors;
    }

    public function actionIndex() {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' =>  Yii::$app->user->id]),
            'pagination' => false
        ]);
        return $activeData;
    }
}
