<?php
namespace api\modules\vp\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\vp\helper\KeyTokenAuth;
class GuideController extends ActiveController
{

    public $modelClass = 'api\modules\vp\models\Guide';
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
    /*
    public function actionUpgrade(){
        $post = \Yii::$app->request->post();

        $cache = \Yii::$app->cache;
        $cache->set('log', $post);
        if(isset($post["data"])){
            $data = json_decode($post["data"], true);
        }else{
            throw new \Exception("no data");
        }
        if(!isset($data['version'])){
            throw new \Exception("no version");

        }
       
        $result = version_compare($data['version'], $version2 = '1.1.9');
        if($result > 0){
            throw new \Exception("version too low" .$result);
        }
        if(!isset($data["scenes"])){
            throw new \Exception("no scenes");
        }
        $scenes = $data["scenes"];
        if(!is_array($scenes)){
            throw new \Exception("scenes not array");
        }
        for($n = 0; $n < count($scenes); $n++){
            $scene = $scenes[$n];
            if(!isset($scene["levels"])){
                throw new \Exception("no scene id");
            }
            $levels = $scene["levels"];
            if(!is_array($levels)){
                throw new \Exception("levels not array");
            }
            for($i = 0; $i < count($levels); $i++){
               $level = $levels[$i];
               $datas = new ActiveDataProvider([
                    'query' => \api\modules\vp\models\Guide::find(),
                    'pagination' => [
                        'pageSize' => false,
                    ]
               ]);
               return $datas;
               

            }
        }
        return $data;
    }*/

    public function actionIndex()
    {
        

        $papeSize = \Yii::$app->request->get('pageSize', 15);
        $page = \Yii::$app->request->get('page', 0);
        $data = new ActiveDataProvider([
            'query' => \api\modules\vp\models\Guide::find(),
            'pagination' => [
                'pageSize' => $papeSize,
            ]
        ]);
        return $data;
       
       
    }
   
}
