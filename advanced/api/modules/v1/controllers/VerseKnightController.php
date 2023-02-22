<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\KnightSearch;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class VerseKnightController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\VerseKnight';
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

    public function actionKnights()
    {

        if (!isset(Yii::$app->request->queryParams['knight_id'])) {
            throw new BadRequestHttpException('缺乏 knight_id 数据');
        }
        $searchModel = new KnightSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $knight_id = HtmlPurifier::process(Yii::$app->request->queryParams['knight_id']);
        $query = $dataProvider->query;
        $query->select('knight.*')->leftJoin('verse_knight', '`verse_knight`.`knight_id` = `knight`.`id`')->andWhere(['verse_knight.verse_id' => $verse_id]);
        return $dataProvider;
    }

}
