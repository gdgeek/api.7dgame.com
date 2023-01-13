<?php
namespace api\modules\p1\controllers;

use api\modules\p1\models\VerseSearch;
use Yii;
use yii\rest\ActiveController;

class VerseOpenController extends ActiveController
{

    public $modelClass = 'api\modules\p1\models\Verse';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // echo $this->action->id;

        // add CORS filter
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

        return [];
    }

    public function actionIndex()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $query->select('verse.*')->leftJoin('verse_open', '`verse_open`.`verse_id` = `verse`.`id`')->andWhere(['NOT', ['verse_open.id' => null]]);
        return $dataProvider;
    }

}
