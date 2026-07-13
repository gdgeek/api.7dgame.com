<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\ResourceSearch;
use api\modules\v1\models\Resource;
use api\modules\v1\models\File;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use OpenApi\Annotations as OA;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

use yii\caching\TagDependency;

/**
 * @OA\Tag(
 *     name="Resource",
 *     description="资源管理接口"
 * )
 */
class ResourceController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Resource';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
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
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionCreate()
    {
        $model = new Resource();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->author_id = (int) Yii::$app->user->id;
        $this->ensureOwnedFiles($model);

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        if (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the resource');
        }

        Yii::$app->response->statusCode = 422;
        return $model;
    }
    
    /**
     * @OA\Get(
     *     path="/v1/resource/{id}",
     *     summary="获取资源详情",
     *     description="根据ID获取资源详情",
     *     tags={"Resource"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="资源ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="资源详情",
     *         @OA\JsonContent(ref="#/components/schemas/Resource")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="资源不存在")
     * )
     */
    public function actionView($id)
    {
        $model = Resource::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Resource not found');
        }
        $this->checkAccess('view', $model);
        return $model;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (
            $model instanceof Resource
            && in_array($action, ['update', 'delete'], true)
            && (int) $model->author_id !== (int) Yii::$app->user->id
        ) {
            throw new ForbiddenHttpException('You are not allowed to access this resource');
        }
    }

    public function actionUpdate($id)
    {
        $model = Resource::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Resource not found');
        }

        $this->checkAccess('update', $model);
        $authorId = $model->author_id;
        $model->load(Yii::$app->request->bodyParams, '');
        $model->author_id = $authorId;
        $this->ensureOwnedFiles($model);

        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the resource');
        }

        return $model;
    }

    protected function ensureOwnedFiles(Resource $model): void
    {
        $fileIds = array_values(array_unique(array_filter([
            (int) $model->file_id,
            (int) $model->image_id,
        ])));

        if ($fileIds === []) {
            return;
        }

        $ownedCount = File::find()
            ->where(['id' => $fileIds, 'user_id' => (int) Yii::$app->user->id])
            ->count();

        if ((int) $ownedCount !== count($fileIds)) {
            throw new ForbiddenHttpException('Resources may only use files owned by the current user');
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/resource",
     *     summary="获取资源列表",
     *     description="获取当前用户的资源列表，支持按类型筛选",
     *     tags={"Resource"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="资源类型",
     *         required=false,
     *         @OA\Schema(type="string", example="image")
     *     ),
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
     *         description="资源列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Resource")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $searchModel = new ResourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        if (isset(Yii::$app->request->queryParams['type'])) {
            $type = HtmlPurifier::process(Yii::$app->request->queryParams['type']);
            $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id, 'type' => $type]);
        } else {
            $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);
        }
        //$dataProvider->query->cache(3600, new TagDependency(['tags' => 'resource_cache']));
        return $dataProvider;
    }

}
