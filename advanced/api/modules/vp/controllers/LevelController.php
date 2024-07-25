<?php
namespace api\modules\vp\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\vp\helper\KeyTokenAuth;
use api\modules\vp\models\Level;
class GuideController extends ActiveController
{

    public $modelClass = 'api\modules\vp\models\Level';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => KeyTokenAuth::className(),
        ];
        return $behaviors;
    }
   
   
   
    public function actionRecode(){
       
        $player_id = \Yii::$app->player->token->id;
        $post = Yii::$app->request->post();
        if(!isset($post["guide_id"])){
            throw new \Exception("no guide_id");
        }
        $guide_id = $post["guide_id"];

        $model = Level::find()->where(['player_id' => $player_id, 'guide_id' => $guide_id]);
       
        if($model == null){
            $model = new Level();
        }  
        $model->load(\Yii::$app->request->post(), '');
        $model->player_id = \Yii::$app->player->token->id;
        $model->save();
        return $model;
        
    }
   
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['options']);
        unset($actions['view']);
        return $actions;
    }
   
}
