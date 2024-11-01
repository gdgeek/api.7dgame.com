<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MetaSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class VerseMetaController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\VerseMeta';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

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

        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionMetas()
    {

        if (!isset(Yii::$app->request->queryParams['verse_id'])) {
            throw new BadRequestHttpException('缺乏 verse_id 数据');
        }
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $verse_id = HtmlPurifier::process(Yii::$app->request->queryParams['verse_id']);
        $query = $dataProvider->query;
        $query->select('meta.*')->leftJoin('verse_meta', '`verse_meta`.`meta_id` = `meta`.`id`')->andWhere(['verse_meta.verse_id' => $verse_id, 'meta.custom' => 1]);
        return $dataProvider;
    }

}
