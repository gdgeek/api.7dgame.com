<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MetaSearch;
use api\modules\v1\models\data\MetaCodeTool;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use api\modules\v1\services\ReliableWrite;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

use yii\base\Exception;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\models\Meta;

/**
 * @OA\Tag(
 *     name="Meta",
 *     description="Meta 元数据管理接口"
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/v1/meta/{id}",
     *     summary="获取 Meta 详情",
     *     description="根据ID获取 Meta 元数据详情",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meta 详情",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionView($id)
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id' => $id, 'prefab' => 0]);
        $model = $dataProvider->query->one();
        if (!$model) {
            throw new NotFoundHttpException('Meta not found');
        }
        $this->checkAccess('view', $model);
        return $model;
    }

    /**
     * @OA\Delete(
     *     path="/v1/meta/{id}",
     *     summary="删除 Meta",
     *     description="删除指定的 Meta 元数据（预制件不可删除）",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="删除成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="禁止删除预制件"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionDelete($id)
    {
        $model = Meta::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Meta not found');
        }
        $this->checkAccess('delete', $model);
        if ($model->prefab == 1) {
            throw new \yii\web\ForbiddenHttpException('You can not delete this item');
        }
        $model->delete();
        return $model;
    }

    /**
     * @OA\Put(
     *     path="/v1/meta/{id}",
     *     summary="更新 Meta",
     *     description="更新指定的 Meta 元数据",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Meta 名称"),
     *             @OA\Property(property="info", type="string", description="Meta 信息（JSON）"),
     *             @OA\Property(property="resource_id", type="integer", description="资源ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionUpdate($id)
    {
        $body = Yii::$app->request->bodyParams;
        return ReliableWrite::run('meta', (int) $id, 'save', $body,
            fn (Meta $model) => $this->checkAccess('update', $model),
            function (Meta $model) use ($body): array {
                $authorId = $model->author_id;
                $model->load($body, '');
                $model->author_id = $authorId;
                $model->prefab = 0;
                if (!$model->save()) {
                    throw new \yii\web\BadRequestHttpException('Invalid editor data; nothing was saved');
                }
                if (!$model->refresh()) {
                    throw new \yii\web\ServerErrorHttpException('Saved object could not be reloaded');
                }
                return $model->toArray();
            }
        );
    }

    /**
     * @OA\Get(path="/v1/metas/create-operations", summary="Read own creation evidence by operation or UUID; never replays creation", tags={"Meta"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="operationId", in="query", @OA\Schema(type="string", format="uuid")),
     * @OA\Parameter(name="creationUuid", in="query", @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Structured completed, observed, not_observed, indeterminate or conflict evidence; UUID readback is not an operation receipt"),
     * @OA\Response(response=400, description="Missing or invalid identifiers"))
     * @OA\Get(path="/v1/metas/create-operations/{operationId}", summary="Read own creation receipt", tags={"Meta"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="operationId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Committed creation identity and writeReceipt"),
     * @OA\Response(response=403, description="Current object permission revoked"),
     * @OA\Response(response=404, description="Creation not observed; does not prove failure"))
     */
    public function actionCreateOperation($receiptOperationId = null): array
    {
        if ($receiptOperationId === null) {
            return \api\modules\v1\services\ReliableCreate::lookup('meta',
                Yii::$app->request->get('operationId'), Yii::$app->request->get('creationUuid'),
                fn (Meta $model) => $this->checkAccess('update', $model));
        }
        return \api\modules\v1\services\ReliableCreate::receipt('meta', (string) $receiptOperationId,
            fn (Meta $model) => $this->checkAccess('update', $model));
    }

    /**
     * @OA\Post(
     *     path="/v1/meta",
     *     summary="创建 Meta",
     *     description="创建新的 Meta 元数据",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Meta 名称", example="My Meta"),
     *             @OA\Property(property="info", type="string", description="Meta 信息（JSON）", example="{}"),
     *             @OA\Property(property="resource_id", type="integer", description="资源ID", example=1)
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
    // Optional UUID Idempotency-Key enables transactional create/receipt; no header retains the legacy API.
    public function actionCreate()
    {
        if (Yii::$app->request->headers->has('Idempotency-Key')) {
            return \api\modules\v1\services\ReliableCreate::run('meta', Yii::$app->request->bodyParams,
                fn (Meta $model) => $this->checkAccess('update', $model));
        }
        $model = new Meta();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->author_id = (int) Yii::$app->user->id;
        $model->prefab = 0;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/meta",
     *     summary="获取 Meta 列表",
     *     description="获取当前用户的 Meta 元数据列表",
     *     tags={"Meta"},
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
     *         description="Meta 列表",
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
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id, 'prefab' => 0]);
        return $dataProvider;
    }
    
    /**
     * @OA\Put(
     *     path="/v1/meta/{id}/code",
     *     summary="更新 Meta 代码",
     *     description="更新 Meta 的代码（Blockly、Lua、JavaScript）",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="blockly", type="string", description="Blockly XML 代码"),
     *             @OA\Property(property="lua", type="string", description="Lua 代码"),
     *             @OA\Property(property="js", type="string", description="JavaScript 代码")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionUpdateCode($id)
    {
        $body = Yii::$app->request->bodyParams;
        return ReliableWrite::run('meta', (int) $id, 'save_code', $body,
            fn (Meta $model) => $this->checkAccess('update', $model),
            function (Meta $owner) use ($body): array {
                $model = new MetaCodeTool($owner->id);
                $model->load($body, '');
                if (!$model->validate()) {
                    throw new \yii\web\BadRequestHttpException('Invalid script; nothing was saved');
                }
                $model->save();
                return $model->toArray();
            }
        );
    }

    /**
     * @OA\Get(path="/v1/metas/{id}/operations/{operationId}", tags={"Meta"},
     *   summary="Read the authenticated editor's durable operation receipt", security={{"Bearer":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="operationId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Committed receipt; no request replay"),
     *   @OA\Response(response=404, description="Not observed; not proof of failure"))
     */
    public function actionOperation($id, $operationId): array
    {
        return ReliableWrite::receipt('meta', (int) $id, (string) $operationId,
            fn (Meta $model) => $this->checkAccess('update', $model));
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (!$model instanceof Meta) {
            return;
        }

        if ($action === 'view' && !$model->viewable) {
            throw new ForbiddenHttpException('You are not allowed to view this entity');
        }

        if (in_array($action, ['update', 'delete'], true) && !$model->editable) {
            throw new ForbiddenHttpException('You are not allowed to modify this entity');
        }
    }
    
}
