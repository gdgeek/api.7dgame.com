<?php
namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use api\modules\v1\models\PhototypeSearch;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Phototype",
 *     description="照片类型管理接口"
 * )
 */
class PhototypeController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Phototype';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
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
            'class' => CompositeAuth::className(),
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options', 'by-type'],
        ];
        if ($this->action->id !== 'by-type') {
            $behaviors['access'] = [
                'class' => AccessControl::class
            ];
        }

        return $behaviors;
    }
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);

        return $actions;
    }
    public function actionByType(string $type)
    {
        $modelClass = $this->modelClass;
        $model = $modelClass::findOne(['type' => $type]);

        if ($model === null) {
            throw new BadRequestHttpException("Phototype with type '{$type}' not found.");
        }

        return $model->toArray(['id', 'data', 'title'], ['resource']);
    }

    public function actionIndex()
    {
        $searchModel = new PhototypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $dataProvider;
    }

}
