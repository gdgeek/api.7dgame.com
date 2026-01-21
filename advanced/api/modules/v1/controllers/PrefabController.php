<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MetaSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Prefab",
 *     description="预制件管理接口"
 * )
 */
class PrefabController extends ActiveController
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

    /**
     * @OA\Get(
     *     path="/v1/prefab/{id}",
     *     summary="获取预制件详情",
     *     description="根据ID获取预制件详情",
     *     tags={"Prefab"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="预制件ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="预制件详情",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="预制件不存在")
     * )
     */
    public function actionView($id)
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id' => $id, 'prefab' => 1]);
        return $dataProvider->query->one();
    }

    /**
     * @OA\Delete(
     *     path="/v1/prefab/{id}",
     *     summary="删除预制件",
     *     description="删除指定的预制件",
     *     tags={"Prefab"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="预制件ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="删除成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="禁止删除非预制件"),
     *     @OA\Response(response=404, description="预制件不存在")
     * )
     */
    public function actionDelete($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        if ($model->prefab == 0) {
          throw new \yii\web\ForbiddenHttpException('You can not delete this item');
        }
        $model->delete();
        return $model;
    }

    /**
     * @OA\Put(
     *     path="/v1/prefab/{id}",
     *     summary="更新预制件",
     *     description="更新指定的预制件",
     *     tags={"Prefab"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="预制件ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="预制件标题"),
     *             @OA\Property(property="data", type="string", description="预制件数据（JSON）"),
     *             @OA\Property(property="info", type="string", description="预制件信息（JSON）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="预制件不存在")
     * )
     */
    public function actionUpdate($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 1;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/prefab",
     *     summary="创建预制件",
     *     description="创建新的预制件",
     *     tags={"Prefab"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", description="预制件标题", example="My Prefab"),
     *             @OA\Property(property="data", type="string", description="预制件数据（JSON）", example="{}"),
     *             @OA\Property(property="info", type="string", description="预制件信息（JSON）", example="{}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="创建成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionCreate()
    {
        $model = new \api\modules\v1\models\Meta();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 1;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/prefab",
     *     summary="获取预制件列表",
     *     description="获取所有预制件列表",
     *     tags={"Prefab"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per-page",
     *         in="query",
     *         description="每页数量",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="预制件列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Meta")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['prefab' => 1]);
        return $dataProvider;
    }
}
