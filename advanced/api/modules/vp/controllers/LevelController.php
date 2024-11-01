<?php
namespace api\modules\vp\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\vp\helper\KeyTokenAuth;
use api\modules\vp\models\Level;
use Yii;
class LevelController extends ActiveController
{

    public $modelClass = 'api\modules\vp\models\Level';
    public function behaviors()
    {
        $cache = \Yii::$app->cache;
        $cache->set('log', ["post" => \Yii::$app->request->post(), "get"=>\Yii::$app->request->get()]);
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
   
    public function actionStars(){

        return \Yii::$app->player->stars();
    
    }
   
    public function actionRecord(){
       
    
        $cache = \Yii::$app->cache;
        $player_id = \Yii::$app->player->token->id;
        $post = Yii::$app->request->post();
        
        if(Yii::$app->request->isGet){
            $data = Yii::$app->request->get();
        
        }else{
            $data = Yii::$app->request->post();
        }
    
        if(!isset($data["guide_id"])){
            throw new \Exception("no guide_id");
        }
    
        $guide_id = $data["guide_id"];

        $model = Level::find()->where(['player_id' => $player_id, 'guide_id' => $guide_id])->one();
    
        if($model == null){
            $model = new Level();
            $model->player_id = \Yii::$app->player->token->id;
            $model->guide_id = $guide_id;
            $model->record = 999;
        }
        $msg = "old record";
        if(isset($data['record']) &&( !isset($model->record) || $data['record'] < $model->record)){
            $model->record = $data['record'];
            $msg = "new record";
        }
        if(isset($data['score'])){
            $model->score = $data['score'];
        }
     
        
        $model->save();
        
        if($model->validate()){
            $model->save();
            return [
                "ret" => true,
                "user_data"=> [
                    "record" => $model->record,
                    "score" => $model->score,
                    "defined"=> true,
                    
                ],
                "stars" => \Yii::$app->player->stars(),
                "msg"=> $msg,
            ];
        }else{
            throw  new \Exception(json_encode($model->errors));
        }
            
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
