<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use api\modules\v1\models\VerseSearch;
use api\modules\v1\models\VerseShare;
use api\modules\v1\models\VerseShareSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class VerseShareController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\VerseShare';
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
        unset($actions['create']);
        unset($actions['index']);
        return $actions;
    }
    public function actionPut()
    {
        return 123;
    }
    public function actionVerses()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $query->select('verse.*')->leftJoin('verse_share', '`verse_share`.`verse_id` = `verse`.`id`')->andWhere(['verse_share.user_id' => Yii::$app->user->id]);
        return $dataProvider;
    }
    public function actionIndex($verse_id)
    {

        $searchModel = new VerseShareSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['verse_id' => $verse_id]);
        $models = $dataProvider->getModels();

        return $models;

    }
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        if (isset($post['username']) && isset($post['verse_id']) && isset($post['editable'])) {
            $user = User::findByUsername($post['username']);
            if (isset($user)) {
                $model = new VerseShare();
                $model->user_id = $user->id;
                $model->verse_id = $post['verse_id'];
                $model->editable = $post['editable'];
                if (isset($post['info'])) {
                    $model->info = $post['info'];
                }
                if ($model->validate() && $model->save()) {
                    return $model;
                } else {
                    if (count($model->errors) == 0) {
                        throw new Exception("未知错误", 400);
                    } else {
                        throw new Exception(json_encode($model->errors), 400);
                    }
                }

            } else {
                throw new Exception("不存在此用户", 400);
            }
        } else {
            throw new Exception("缺少数据", 400);
        }return 0;

    }
}
