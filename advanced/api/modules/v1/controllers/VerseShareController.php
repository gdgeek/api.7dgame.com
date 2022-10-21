<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use api\modules\v1\models\VerseShare;
use api\modules\v1\models\VerseShareSearch;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class VerseShareController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\VerseShare';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
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
        unset($actions['delete']);
        return $actions;
    }
    public function actionDelete($id, $user_id, $verse_id)
    {

        $model = null;
        if (isset($verse_id)) {
            $model = VerseShare::findOne(['verse_id' => $verse_id, 'user_id' => $user_id]);
        }

        if ($model == null) {
            throw new BadRequestHttpException('无效id');
        }
        $id = $model->id;
        $model->delete();
        return $id;

    }
    public function actionMe()
    {

    }
    public function actionIndex()
    {
        $get = Yii::$app->request->get();
        if (isset($get['verse_id'])) {
            $searchModel = new VerseShareSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['verse_id' => $get['verse_id']]);
            $models = $dataProvider->getModels();
            $results = [];
            foreach ($models as $model) {
                $sample = $model->user;
                $sample['info'] = $model->info;
                array_push($results, $sample);
            }
            return $results;
        }
        return $get;
    }
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        if (isset($post['username']) && isset($post['verse_id'])) {
            $user = User::findByUsername($post['username']);
            if (isset($user)) {
                $model = new VerseShare();
                $model->user_id = $user->id;
                $model->verse_id = $post['verse_id'];
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
        }

        return 0;
    }
}
