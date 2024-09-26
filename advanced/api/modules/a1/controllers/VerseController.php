<?php
namespace api\modules\a1\controllers;
use api\modules\a1\models\VerseSearch;
use yii\web\BadRequestHttpException;
use api\modules\v1\models\VerseReleaseSearch;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use Yii;
class VerseController extends ActiveController
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
        //  unset($actions['view']);
        return $actions;
    }
    
    public function actionOpen()
    {
        $searchModel = new VerseSearch();
        $papeSize = \Yii::$app->request->get('pageSize', 15);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,  $papeSize);
        $query = $dataProvider->query;
        $query->select('verse.*')->leftJoin('verse_open', '`verse_open`.`verse_id` = `verse`.`id`')->andWhere(['NOT', ['verse_open.id' => null]]);
        return $dataProvider;
    }
    public function actionRelease(){
        if (!isset(Yii::$app->request->queryParams['code'])) {
            throw new BadRequestHttpException('缺乏 code 数据');
        }
        $code = Yii::$app->request->queryParams['code'];
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        
        $query->select('verse.*')->leftJoin('verse_release', '`verse_release`.`verse_id` = `verse`.`id`')->andWhere(['verse_release.code' => $code]);
        return $query->one();
    }
    
}
