<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MetaSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class MetaController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Meta';
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
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }
    public function actionView($id)
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id' => $id, 'prefab' => 0]);
        return $dataProvider->query->one();
    }
    public function actionDelete($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        if ($model->prefab == 1) {
          throw new \yii\web\ForbiddenHttpException('You can not delete this item');
        }
        $model->delete();
        return $model;
    }
    public function actionUpdate($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 0;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }
    public function actionCreate()
    {
        $model = new \api\modules\v1\models\Meta();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 0;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }
    public function actionIndex()
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id, 'prefab' => 0]);
        return $dataProvider;
    }

}
