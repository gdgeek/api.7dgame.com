<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Property;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseSearch;
use api\modules\v1\models\data\VerseCodeTool;
use bizley\jwt\JwtHttpBearerAuth;
use common\components\security\CorsOriginPolicy;
use yii\db\ActiveQuery;
use yii\db\Query;
use mdm\admin\components\AccessControl;
use Yii;
use api\modules\v1\services\ReliableWrite;
use api\modules\v1\services\ScenePublication;
use api\modules\v1\services\SceneProperties;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseTags;
use yii\base\Exception;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Verse",
 *     description="Verse 管理接口"
 * )
 */
class VerseController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Verse';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => CorsOriginPolicy::yiiConfiguration(),
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                [
                    'class' => JwtHttpBearerAuth::class,
                    // Malformed, expired and invalid JWTs must all converge on
                    // CompositeAuth's HTTP 401 path instead of leaking parser
                    // exceptions as HTTP 500 responses.
                    'throwException' => false,
                ],
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            // Scene associations require admin/root; dictionary creation and deletion require root.
            'allowActions' => ['properties', 'property-options', 'add-property', 'remove-property',
                'property-usage', 'delete-property-globally', 'global-property-options', 'by-property', 'by-property-scene'],
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return array_merge(parent::verbs(), [
            'properties' => ['GET', 'HEAD'],
            'property-options' => ['GET', 'HEAD'],
            'add-property' => ['POST'],
            'remove-property' => ['DELETE'],
            'property-usage' => ['GET', 'HEAD'],
            'delete-property-globally' => ['DELETE'],
            'global-property-options' => ['GET', 'HEAD'],
            'by-property' => ['GET', 'HEAD'],
            'by-property-scene' => ['GET', 'HEAD'],
        ]);
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/properties", summary="Admin 或 root 查看场景属性", tags={"Verse"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="场景已关联的属性，按 ID 排序", @OA\JsonContent(type="array", @OA\Items(
     *   @OA\Property(property="id", type="integer"), @OA\Property(property="key", type="string"),
     *   @OA\Property(property="info", nullable=true, description="属性信息，兼容历史 JSON 值")))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 admin 或 root 可访问"),
     * @OA\Response(response=404, description="场景不存在"))
     */
    public function actionProperties($id): array
    {
        $this->requirePropertyManager();
        return SceneProperties::listing($id);
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/property-options", summary="Admin 或 root 查看可选属性目录", tags={"Verse"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="全局属性目录，按 key、ID 升序排列，含场景已关联的属性", @OA\JsonContent(type="array", @OA\Items(
     *   @OA\Property(property="id", type="integer"), @OA\Property(property="key", type="string"),
     *   @OA\Property(property="info", nullable=true, description="属性信息，兼容历史 JSON 值")))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 admin 或 root 可访问"),
     * @OA\Response(response=404, description="场景不存在"))
     */
    public function actionPropertyOptions($id): array
    {
        $this->requirePropertyManager();
        return SceneProperties::options($id);
    }

    /**
     * @OA\Get(path="/v1/verses/global-property-options", summary="Root 查看全局属性筛选目录", tags={"Verse"}, security={{"Bearer": {}}},
     * description="无需指定场景。返回完整属性字典，按 key、ID 排序，供全局场景筛选使用。",
     * @OA\Response(response=200, description="全局属性数组", @OA\JsonContent(type="array", @OA\Items(
     *   @OA\Property(property="id", type="integer"), @OA\Property(property="key", type="string"),
     *   @OA\Property(property="info", nullable=true)))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 root 可访问"))
     */
    public function actionGlobalPropertyOptions(): array
    {
        $this->requirePropertyRoot();
        return SceneProperties::globalOptions();
    }

    /**
     * @OA\Get(path="/v1/verses/by-property", summary="Root 按属性查询全局场景", tags={"Verse"}, security={{"Bearer": {}}},
     * description="显式全局范围，包含其他作者的私有场景；历史重复关联只返回一次。支持现有 VerseSearch[...]、sort、page、per-page、expand，以及 tags/public 过滤；响应及分页头与现有场景列表一致。普通 GET /v1/verses 仍只返回我的场景。",
     * @OA\Parameter(name="propertyId", in="query", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="-id")),
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     * @OA\Parameter(name="per-page", in="query", @OA\Schema(type="integer", minimum=1)),
     * @OA\Parameter(name="expand", in="query", @OA\Schema(type="string", example="author,image,public")),
     * @OA\Response(response=200, description="全局关联场景；无人使用的属性返回空数组", @OA\JsonContent(type="array", @OA\Items(type="object"))),
     * @OA\Response(response=400, description="propertyId 缺失或不是正整数"),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 root 可访问"),
     * @OA\Response(response=404, description="属性不存在"))
     */
    public function actionByProperty(): \yii\data\ActiveDataProvider
    {
        $this->requirePropertyRoot();
        $value = Yii::$app->request->get('propertyId');
        if ((!is_int($value) && (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value)))
            || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new BadRequestHttpException('propertyId must be a positive integer');
        }
        $propertyId = (int) $value;
        if (!Property::find()->where(['id' => $propertyId])->exists()) {
            throw new NotFoundHttpException('Property not found');
        }
        $dataProvider = (new VerseSearch())->search(Yii::$app->request->queryParams);
        $association = (new Query())->select('property_filter.verse_id')->from(['property_filter' => VerseProperty::tableName()])
            ->where('property_filter.verse_id = verse.id')
            ->andWhere(['property_filter.property_id' => $propertyId]);
        $dataProvider->query->andWhere(['exists', $association]);
        $this->applyPublicFilter($dataProvider->query);
        $this->applyTagsFilter($dataProvider->query);
        return $dataProvider;
    }

    /**
     * @OA\Get(path="/v1/verses/by-property/{id}", summary="Root 查看全局属性筛选中的场景详情", tags={"Verse"}, security={{"Bearer": {}}},
     * description="显式全局只读入口，允许 root 查看其他作者的私有场景。场景必须仍关联 propertyId；支持 expand。普通场景详情入口的既有 viewable 边界不变。",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Parameter(name="propertyId", in="query", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Parameter(name="expand", in="query", @OA\Schema(type="string", example="author,image,public")),
     * @OA\Response(response=200, description="匹配属性的场景详情", @OA\JsonContent(type="object")),
     * @OA\Response(response=400, description="propertyId 缺失或无效"), @OA\Response(response=401, description="未认证"),
     * @OA\Response(response=403, description="仅 root 可访问"), @OA\Response(response=404, description="属性、场景不存在或场景未关联该属性"))
     */
    public function actionByPropertyScene($id): Verse
    {
        $dataProvider = $this->actionByProperty();
        $verse = $dataProvider->query->andWhere(['verse.id' => $id])->one();
        if ($verse === null) {
            throw new NotFoundHttpException('Scene not associated with property');
        }
        return $verse;
    }

    /**
     * @OA\Post(path="/v1/verses/{id}/properties", summary="Admin 或 root 关联已有属性；仅 root 可通过 key 创建属性", tags={"Verse"}, security={{"Bearer": {}}},
     * description="admin 或 root 通过 propertyId 关联已有属性；只有 root 可通过 key 和可选 info 新建或复用属性（key 已存在也要求 root）；两种形式互斥。已有 key 复用全局属性，不覆盖已有 info；重复关联幂等。添加 public 会使场景公开。",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(required=true, @OA\JsonContent(oneOf={
     *   @OA\Schema(required={"propertyId"}, not=@OA\Schema(anyOf={@OA\Schema(required={"key"}), @OA\Schema(required={"info"})}),
     *     @OA\Property(property="propertyId", type="integer", minimum=1)),
     *   @OA\Schema(required={"key"}, not=@OA\Schema(required={"propertyId"}),
     *     @OA\Property(property="key", type="string", minLength=1, maxLength=255),
     *     @OA\Property(property="info", type="string", nullable=true, description="可选；最多 65535 字节"))})),
     * @OA\Response(response=200, description="新增或已关联成功", @OA\JsonContent(
     *   @OA\Property(property="success", type="boolean", example=true),
     *   @OA\Property(property="property", type="object", @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="key", type="string"), @OA\Property(property="info", nullable=true)))),
     * @OA\Response(response=400, description="propertyId、key 或 info 无效，或混用两种形式"), @OA\Response(response=401, description="未认证"),
     * @OA\Response(response=403, description="propertyId 关联仅 admin 或 root；key 形式仅 root（包括复用已有 key）"), @OA\Response(response=404, description="场景或属性不存在"))
     */
    public function actionAddProperty($id): array
    {
        $body = Yii::$app->request->bodyParams;
        if (is_array($body) && array_key_exists('propertyId', $body)
            && !array_key_exists('key', $body) && !array_key_exists('info', $body)) {
            $this->requirePropertyManager();
        } else {
            // The key form may create global dictionary data, so it always
            // requires root even when the supplied key already exists.
            $this->requirePropertyRoot();
        }
        return ['success' => true, 'property' => SceneProperties::add($id, $body)];
    }

    /**
     * @OA\Delete(path="/v1/verses/{id}/properties/{propertyId}", summary="Admin 或 root 从场景移除属性", tags={"Verse"}, security={{"Bearer": {}}},
     * description="仅移除当前场景关联，保留全局属性与其他场景关联；重复移除幂等。移除 public 会取消场景公开。",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="propertyId", in="path", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Response(response=200, description="关联已移除或原本未关联", @OA\JsonContent(
     *   @OA\Property(property="success", type="boolean", example=true),
     *   @OA\Property(property="property", type="object", @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="key", type="string"), @OA\Property(property="info", nullable=true)))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 admin 或 root 可访问"),
     * @OA\Response(response=404, description="场景或全局属性不存在"))
     */
    public function actionRemoveProperty($id, $propertyId): array
    {
        $this->requirePropertyManager();
        return ['success' => true, 'property' => SceneProperties::remove($id, $propertyId)];
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/properties/{propertyId}/usage", summary="Root 查看属性的全局使用数量", tags={"Verse"}, security={{"Bearer": {}}},
     * description="返回全局属性与关联场景数，包括其他作者的场景，历史重复关联只计一次。当前场景无需已关联该属性；数量为提示值，删除时会重新计算。",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="propertyId", in="path", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Response(response=200, description="全局属性使用情况", @OA\JsonContent(
     *   @OA\Property(property="property", type="object", @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="key", type="string"), @OA\Property(property="info", nullable=true)),
     *   @OA\Property(property="sceneCount", type="integer", minimum=0))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 root 可访问"),
     * @OA\Response(response=404, description="场景或全局属性不存在"))
     */
    public function actionPropertyUsage($id, $propertyId): array
    {
        $this->requirePropertyRoot();
        return SceneProperties::usage($id, $propertyId);
    }

    /**
     * @OA\Delete(path="/v1/verses/{id}/properties/{propertyId}/global", summary="Root 全局删除属性及全部场景关联", tags={"Verse"}, security={{"Bearer": {}}},
     * description="不可恢复地删除全局属性及其全部场景关联，保留场景和其他属性。删除 public 会使所有关联场景变为私有。当前场景无需已关联该属性；返回事务内实际影响的不同场景数。",
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="propertyId", in="path", required=true, @OA\Schema(type="integer", minimum=1)),
     * @OA\Response(response=200, description="属性及所有场景关联已删除", @OA\JsonContent(
     *   @OA\Property(property="success", type="boolean", example=true),
     *   @OA\Property(property="property", type="object", @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="key", type="string"), @OA\Property(property="info", nullable=true)),
     *   @OA\Property(property="affectedSceneCount", type="integer", minimum=0))),
     * @OA\Response(response=401, description="未认证"), @OA\Response(response=403, description="仅 root 可访问"),
     * @OA\Response(response=404, description="场景或全局属性不存在，含重复删除"))
     */
    public function actionDeletePropertyGlobally($id, $propertyId): array
    {
        $this->requirePropertyRoot();
        return ['success' => true] + SceneProperties::deleteGlobally($id, $propertyId);
    }

    private function requirePropertyRoot(): void
    {
        if (!Yii::$app->user->can('root')) {
            throw new ForbiddenHttpException('Only root can manage scene properties');
        }
    }

    private function requirePropertyManager(): void
    {
        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Only admin or root can manage scene property associations');
        }
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        if (Yii::$app->request->headers->has('Idempotency-Key')) unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /** UUID Idempotency-Key opts in; callers without it retain Yii's original create action. */
    public function actionCreate(): array
    {
        return \api\modules\v1\services\ReliableCreate::run('verse', Yii::$app->request->bodyParams,
            fn (Verse $model) => $this->checkAccess('update', $model));
    }

    /**
     * @OA\Get(path="/v1/verses/create-operations", summary="Read own creation evidence by operation or UUID; never replays creation", tags={"Verse"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="operationId", in="query", @OA\Schema(type="string", format="uuid")),
     * @OA\Parameter(name="creationUuid", in="query", @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Structured completed, observed, not_observed, indeterminate or conflict evidence; UUID readback is not an operation receipt"),
     * @OA\Response(response=400, description="Missing or invalid identifiers"))
     * @OA\Get(path="/v1/verses/create-operations/{operationId}", summary="Read own creation receipt", tags={"Verse"}, security={{"Bearer": {}}},
     * @OA\Parameter(name="operationId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Committed creation identity and writeReceipt"),
     * @OA\Response(response=403, description="Current object permission revoked"),
     * @OA\Response(response=404, description="Creation not observed; does not prove failure"))
     */
    public function actionCreateOperation($receiptOperationId = null): array
    {
        if ($receiptOperationId === null) {
            return \api\modules\v1\services\ReliableCreate::lookup('verse',
                Yii::$app->request->get('operationId'), Yii::$app->request->get('creationUuid'),
                fn (Verse $model) => $this->checkAccess('update', $model));
        }
        return \api\modules\v1\services\ReliableCreate::receipt('verse', (string) $receiptOperationId,
            fn (Verse $model) => $this->checkAccess('update', $model));
    }

    public function actionUpdate($id)
    {
        $body = Yii::$app->request->bodyParams;
        return ReliableWrite::run('verse', (int) $id, 'save', $body,
            fn (Verse $model) => $this->checkAccess('update', $model),
            function (Verse $model) use ($body): array {
                $authorId = $model->author_id;
                $model->load($body, '');
                $model->author_id = $authorId;
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
     * @OA\Get(
     *     path="/v1/verse/public",
     *     summary="获取公开 Verse 列表",
     *     description="按 property.key=public 获取公开 Verse 列表；tags 仅筛选分类标签",
     *     tags={"Verse"},
     *     @OA\Parameter(name="public", in="query", description="公开属性筛选：1/true 或 0/false；公开列表传 0/false 返回空列表", @OA\Schema(type="string", enum={"0","1","false","true"}, example="1")),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="标签ID列表（逗号分隔）",
     *         required=false,
     *         @OA\Schema(type="string", example="1,2,3")
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
     *         description="公开 Verse 列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Verse ID", example=1),
     *                 @OA\Property(property="name", type="string", description="Verse 名称", example="My Verse"),
     *                 @OA\Property(property="uuid", type="string", description="Verse UUID"),
     *                 @OA\Property(property="author_id", type="integer", description="作者ID"),
     *                 @OA\Property(property="description", type="string", description="描述"),
     *                 @OA\Property(property="image_id", type="integer", description="预览图片ID")
     *             )
     *         )
     *     )
     * )
     */
    public function actionPublic()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        if ($dataProvider->query instanceof ActiveQuery) {
            $query = $dataProvider->query;
            $query->andWhere(['exists', $this->publicPropertyQuery()]);
        }

        $this->applyPublicFilter($dataProvider->query);
        $this->applyTagsFilter($dataProvider->query);

        return $dataProvider;
    }

    /**
     * @OA\Get(
     *     path="/v1/verse",
     *     summary="获取我的 Verse 列表",
     *     description="获取当前用户创建的 Verse 列表",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(name="public", in="query", description="按公开属性筛选，只接受 1/true 或 0/false；不改变当前用户范围", @OA\Schema(type="string", enum={"0","1","false","true"}, example="1")),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="标签ID列表（逗号分隔）",
     *         required=false,
     *         @OA\Schema(type="string", example="1,2,3")
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
     *         description="Verse 列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Verse ID"),
     *                 @OA\Property(property="name", type="string", description="Verse 名称"),
     *                 @OA\Property(property="uuid", type="string", description="Verse UUID"),
     *                 @OA\Property(property="author_id", type="integer", description="作者ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $searchModel = new VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $this->applyCurrentUserFilter(
            $dataProvider->query,
            (int) Yii::$app->user->id
        );

        // 处理标签过滤
        $this->applyPublicFilter($dataProvider->query);
        $this->applyTagsFilter($dataProvider->query);

        return $dataProvider;
    }

    /**
     * Keep the My Scenes boundary independent from client-supplied filters.
     */
    protected function applyCurrentUserFilter(ActiveQuery $query, int $userId): void
    {
        $query->andWhere(['verse.author_id' => $userId]);
    }

    /**
     * @OA\Put(
     *     path="/v1/verse/{id}/code",
     *     summary="更新 Verse 代码",
     *     description="更新 Verse 的代码（Blockly、Lua、JavaScript）",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Verse ID",
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
     *     @OA\Response(response=404, description="Verse 不存在")
     * )
     */
    public function actionUpdateCode($id)
    {
        $body = Yii::$app->request->bodyParams;
        return ReliableWrite::run('verse', (int) $id, 'save_code', $body,
            fn (Verse $model) => $this->checkAccess('update', $model),
            function (Verse $owner) use ($body): array {
                $model = new VerseCodeTool($owner->id);
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
     * 为 verse 添加标签
     * POST /verse/{id}/tag?tags_id={tags_id}
     * @param int $id verse ID
     * @param int $tags_id 标签 ID
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     * 
     * @OA\Post(
     *     path="/v1/verse/{id}/tag",
     *     summary="添加标签",
     *     description="为 Verse 添加分类标签；public 是场景属性，传入旧 public 标签 ID 返回 400",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Verse ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="tags_id",
     *         in="query",
     *         description="标签ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="添加成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag added")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Verse 或标签不存在")
     * )
     */
    public function actionAddTag($id, $tags_id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }
        $this->checkAccess('update', $verse);

        $tags = Tags::findOne($tags_id);
        if (!$tags) {
            throw new Exception('Tags not found');
        }
        if ($tags->key === 'public') {
            throw new BadRequestHttpException('Public visibility is a scene property; use the public endpoint.');
        }

        $model = new VerseTags();
        $model->verse_id = $id;
        $model->tags_id = $tags_id;

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Tag added'
            ];
        } else {
            throw new Exception(json_encode($model->getErrors()));
        }
    }

    /**
     * 移除 verse 的标签
     * DELETE /verse/{id}/tag?tags_id={tags_id}
     * @param int $id verse ID
     * @param int $tags_id 标签 ID
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     * 
     * @OA\Delete(
     *     path="/v1/verse/{id}/tag",
     *     summary="移除标签",
     *     description="从 Verse 移除标签",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Verse ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="tags_id",
     *         in="query",
     *         description="标签ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="移除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag removed")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Verse 或标签关联不存在")
     * )
     */
    public function actionRemoveTag($id, $tags_id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }
        $this->checkAccess('update', $verse);

        $model = VerseTags::find()->where(['verse_id' => $id, 'tags_id' => $tags_id])->one();
        if ($model) {
            $model->delete();
            return [
                'success' => true,
                'message' => 'Tag removed'
            ];
        }
        throw new Exception('VerseTags not found');
    }

    /**
     * 为 verse 创建快照
     * POST /verse/{id}/take-photo
     * @param int $id verse ID
     * @return array
     * @throws Exception
     * 
     * @OA\Post(
     *     path="/v1/verse/{id}/take-photo",
     *     summary="创建快照",
     *     description="为 Verse 创建快照（保存当前状态）",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Verse ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="快照创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="快照ID"),
     *             @OA\Property(property="uuid", type="string", description="快照UUID"),
     *             @OA\Property(property="name", type="string", description="快照名称"),
     *             @OA\Property(property="description", type="string", description="快照描述"),
     *             @OA\Property(property="data", type="string", description="快照数据（JSON）"),
     *             @OA\Property(
     *                 property="space",
     *                 type="object",
     *                 nullable=true,
     *                 description="绑定的空间快照，只包含 type、image、mesh、file",
     *                 @OA\Property(property="type", type="string", description="定位类型", enum={"immersal", "area-target-scanner"}),
     *                 @OA\Property(property="image", ref="#/components/schemas/File", nullable=true),
     *                 @OA\Property(property="mesh", ref="#/components/schemas/File", nullable=true),
     *                 @OA\Property(property="file", ref="#/components/schemas/File", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Verse 不存在")
     * )
     */
    public function actionTakePhoto($id)
    {
        $body = Yii::$app->request->bodyParams;
        // Script language is part of publication intent and therefore the idempotency key.
        $language = Yii::$app->request->get('cl', 'lua');
        if (!in_array($language, ['lua', 'js'], true)) {
            throw new BadRequestHttpException('Unsupported publication language');
        }
        $body['_publicationLanguage'] = $language;
        return ReliableWrite::run('verse', (int) $id, 'publish', $body,
            fn (Verse $model) => $this->checkAccess('update', $model),
            fn (Verse $model) => ScenePublication::publish($model));
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/operations/{operationId}", tags={"Verse"},
     *   summary="Read the authenticated editor's durable operation receipt", security={{"Bearer":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="operationId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Committed receipt; no request replay"),
     *   @OA\Response(response=404, description="Not observed; not proof of failure"))
     */
    public function actionOperation($id, $operationId): array
    {
        return ReliableWrite::receipt('verse', (int) $id, (string) $operationId,
            fn (Verse $model) => $this->checkAccess('update', $model));
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/publication", tags={"Verse"},
     *   summary="Read authoritative publication metadata", security={{"Bearer":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Current published state and snapshot pointer; no historical version"))
     */
    public function actionPublication($id): array
    {
        return ScenePublication::read((int) $id,
            fn (Verse $model) => $this->checkAccess('update', $model));
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/publications", tags={"Verse"},
     *   summary="List immutable publications and bounded capacity metadata", security={{"Bearer":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", minimum=1, maximum=50)),
     *   @OA\Parameter(name="before", in="query", @OA\Schema(type="integer", minimum=0)),
     *   @OA\Response(response=200, description="Retained metadata, capacity and retention policy; default latest 20 bodies"))
     */
    public function actionPublications($id): array
    {
        $limit = filter_var(Yii::$app->request->get('limit', 20), FILTER_VALIDATE_INT);
        $before = filter_var(Yii::$app->request->get('before', 0), FILTER_VALIDATE_INT);
        if ($limit === false || $before === false) throw new BadRequestHttpException('Invalid history pagination');
        return \api\modules\v1\services\PublicationArchive::listing((int) $id,
            fn (Verse $model) => $this->checkAccess('update', $model), $limit, $before);
    }

    /**
     * @OA\Get(path="/v1/verses/{id}/publications/{publicationVersionId}", tags={"Verse"},
     *   summary="Read exact archived UTF-8 JSON bytes and their SHA-256", security={{"Bearer":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="publicationVersionId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Integrity-checked archive; file bytes are not retained"),
     *   @OA\Response(response=403, description="Current scene edit permission required"),
     *   @OA\Response(response=404, description="Version not found for this scene"),
     *   @OA\Response(response=410, description="publication_version_expired: body removed by retention policy"),
     *   @OA\Response(response=500, description="Archive integrity failure"))
     */
    public function actionPublicationVersion($id, $publicationVersionId): array
    {
        return \api\modules\v1\services\PublicationArchive::read((int) $id, (string) $publicationVersionId,
            fn (Verse $model) => $this->checkAccess('update', $model));
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (!$model instanceof Verse) {
            return;
        }

        if ($action === 'view' && !$model->viewable) {
            throw new ForbiddenHttpException('You are not allowed to view this scene');
        }

        if (in_array($action, ['update', 'delete'], true) && !$model->editable) {
            throw new ForbiddenHttpException('You are not allowed to modify this scene');
        }
    }

    private function findVerse($id): Verse
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }
        return $verse;
    }

    /**
     * 应用标签过滤到查询
     * @param \yii\db\ActiveQuery $query 查询对象
     */
    private function applyTagsFilter($query)
    {
        $tags = Yii::$app->request->get('tags');
        if ($tags) {
            $tagsArray = array_map('intval', explode(',', $tags));
            if (!empty($tagsArray)) {
                $classifications = (new Query())->select('vt_filter.verse_id')
                    ->from(['vt_filter' => VerseTags::tableName()])
                    ->innerJoin(['tag_filter' => Tags::tableName()], 'tag_filter.id = vt_filter.tags_id')
                    ->where('vt_filter.verse_id = verse.id')
                    ->andWhere(['vt_filter.tags_id' => $tagsArray])
                    ->andWhere(['or', ['<>', 'tag_filter.key', 'public'], ['tag_filter.key' => null]]);
                $query->andWhere(['exists', $classifications]);
            }
        }
    }

    /** Public visibility is independent from classification tags. */
    private function applyPublicFilter(ActiveQuery $query): void
    {
        $params = Yii::$app->request->queryParams;
        if (!array_key_exists('public', $params)) {
            return;
        }

        $value = $params['public'];
        if (in_array($value, [true, 1, '1', 'true'], true)) {
            $public = true;
        } elseif (in_array($value, [false, 0, '0', 'false'], true)) {
            $public = false;
        } else {
            throw new BadRequestHttpException('public must be 1, 0, true or false.');
        }

        $query->andWhere([$public ? 'exists' : 'not exists', $this->publicPropertyQuery()]);
    }

    private function publicPropertyQuery(): Query
    {
        return (new Query())->select('vp_filter.verse_id')
            ->from(['vp_filter' => VerseProperty::tableName()])
            ->innerJoin(['property_filter' => Property::tableName()], 'property_filter.id = vp_filter.property_id')
            ->where('vp_filter.verse_id = verse.id')
            ->andWhere(['property_filter.key' => 'public']);
    }
}
