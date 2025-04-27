<?php
namespace api\modules\a1\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\a1\models\VpKeyValueSearch;

class VpKeyValueController extends ActiveController
{

    public $modelClass = 'api\modules\a1\models\VpKeyValue';
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
    public function actionIndex()
    {
      
        $searchModel = new VpKeyValueSearch();
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
       // $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);
        return $dataProvider;
    }

   /*
    public function actionIndex()
    {
        
        $get = \Yii::$app->request->get();
        if(isset($get['version'])){
            $cache = \Yii::$app->cache;
            if(isset($get['refresh'])){
                unset($get['refresh']);
                $key = json_encode($get);
                $cache->delete($key);
            }
            $key = json_encode($get);
            $data = $cache->get($key);
          
            if ($data === false) {
               
                $papeSize = \Yii::$app->request->get('pageSize', 15);
                $data = new ActiveDataProvider([
                    'query' => \api\modules\a1\models\VpGuide::find(),
                    'pagination' => [
                        'pageSize' => $papeSize,
                    ]
                ]);
                $cache->set($key, $data);
            }
            return $data;
        }

        $papeSize = \Yii::$app->request->get('pageSize', 15);
        $data = new ActiveDataProvider([
            'query' => \api\modules\a1\models\VpGuide::find(),
            'pagination' => [
                'pageSize' => $papeSize,
            ]
        ]);
        return $data;
       
       
    }*/

}
