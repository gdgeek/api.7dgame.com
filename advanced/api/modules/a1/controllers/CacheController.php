<?php
namespace api\modules\a1\controllers;

use yii\rest\ActiveController;
use Yii;
use api\modules\a1\models\VerseSearch;

class CacheController extends ActiveController
{

    public $modelClass = 'api\modules\a1\models\Verse';
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
    public function actionView($id){

        $get = Yii::$app->request->get();
        $version = \Yii::$app->request->get('version');
       
        if(isset($get['version'])){
            $key = $id .':'. json_encode($get['expand']);
            $cache = Yii::$app->cache;
            $data = $cache->get($key);
            if(isset($get['refresh']) && $data !== false){
                $cache->delete($key);
            }
            if ($data === false) {
                $searchModel = new VerseSearch();
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                $dataProvider->query->andWhere(['id' => $id]);
                $data = $dataProvider->query->one();
                $cache->set($key, $data);
            }
            return $data;
        }
    
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id' => $id]);
        return $dataProvider->query->one();
       
    }

}
