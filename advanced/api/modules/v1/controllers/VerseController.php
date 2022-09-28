<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\VerseSearch;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class VerseController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Verse';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // echo $this->action->id;
        if ($this->action->id != 'open' && $this->action->id != 'view') {
            $behaviors['authenticator'] = [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    JwtHttpBearerAuth::class,
                ],
            ];
        }
        $auth = $behaviors['authenticator'];
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

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        if ($this->action->id != 'open' && $this->action->id != 'view') {
            $behaviors['access'] = [
                'class' => AccessControl::class,
            ];
        }

        return $behaviors;
    }
/*
public function actions()
{
$actions = parent::actions();
unset($actions['index']);

return $actions;
}*/

    public function actionPublish()
    {

        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);
        return $dataProvider;
    }

    public function actionOpen()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $query->select('verse.*')->leftJoin('verse_open', '`verse_open`.`verse_id` = `verse`.`id`')->andWhere(['NOT', ['verse_open.id' => null]]);
        return $dataProvider;
    }

    public function actionShare()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $query->select('verse.*')->leftJoin('verse_share', '`verse_share`.`verse_id` = `verse`.`id`')->andWhere(['verse_share.user_id' => Yii::$app->user->id]);
        return $dataProvider;
    }

}
