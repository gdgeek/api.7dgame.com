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
use mdm\admin\components\AccessControl;
use Yii;
use api\modules\v1\services\ReliableWrite;
use api\modules\v1\services\ScenePublication;
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
        ];

        return $behaviors;
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
     *     description="获取所有公开的 Verse 列表",
     *     tags={"Verse"},
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
            $query->innerJoin('verse_property AS vp1', 'vp1.verse_id = verse.id')
                ->innerJoin('property', 'property.id = vp1.property_id')
                ->andWhere(['property.key' => 'public']);
        }

        /*
        // 添加 public 标签条件
        $dataProvider->query->innerJoin('verse_tags AS vt_public', 'vt_public.verse_id = verse.id')
            ->innerJoin('tags AS t_public', 't_public.id = vt_public.tags_id')
            ->andWhere(['t_public.key' => 'public']);*/

        // 处理额外的标签过滤
        $this->applyTagsFilter($dataProvider->query, 'vt_extra');

        return $dataProvider;
    }

    /**
     * @OA\Get(
     *     path="/v1/verse",
     *     summary="获取我的 Verse 列表",
     *     description="获取当前用户创建的 Verse 列表",
     *     tags={"Verse"},
     *     security={{"Bearer": {}}},
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
     * 为 verse 添加 public 属性
     * POST /verse/{id}/add-public
     * @param int $id verse ID
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * 
     * @OA\Post(
     *     path="/v1/verse/{id}/public",
     *     summary="添加公开属性",
     *     description="为 Verse 添加 public 属性，使其公开可见",
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
     *         description="添加成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Public property added")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="无权限"),
     *     @OA\Response(response=404, description="Verse 不存在")
     * )
     */
    public function actionAddPublic($id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }
        $this->checkAccess('update', $verse);

        // 获取或创建 public 属性
        $property = Property::findOne(['key' => 'public']);
        if (!$property) {
            $property = new Property();
            $property->key = 'public';
            $property->info = 'Public visibility';
            if (!$property->save()) {
                throw new BadRequestHttpException(json_encode($property->errors));
            }
        }

        // 检查是否已存在关联
        $existing = VerseProperty::findOne([
            'verse_id' => $id,
            'property_id' => $property->id,
        ]);

        if ($existing) {
            return ['success' => true, 'message' => 'Already public'];
        }

        // 创建关联
        $verseProperty = new VerseProperty();
        $verseProperty->verse_id = $id;
        $verseProperty->property_id = $property->id;

        if (!$verseProperty->save()) {
            throw new BadRequestHttpException(json_encode($verseProperty->errors));
        }

        return ['success' => true, 'message' => 'Public property added'];
    }

    /**
     * 删除 verse 的 public 属性
     * POST /verse/remove-public?id=xxx
     * @param int $id verse ID
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * 
     * @OA\Delete(
     *     path="/v1/verse/{id}/public",
     *     summary="移除公开属性",
     *     description="移除 Verse 的 public 属性，使其不再公开",
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
     *         description="移除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Public property removed")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="无权限"),
     *     @OA\Response(response=404, description="Verse 不存在")
     * )
     */
    public function actionRemovePublic($id)
    {
        $verse = Verse::findOne($id);
        if (!$verse) {
            throw new NotFoundHttpException('Verse not found');
        }
        $this->checkAccess('update', $verse);

        // 获取 public 属性
        $property = Property::findOne(['key' => 'public']);
        if (!$property) {
            return ['success' => true, 'message' => 'No public property exists'];
        }

        // 删除关联
        $deleted = VerseProperty::deleteAll([
            'verse_id' => $id,
            'property_id' => $property->id,
        ]);

        if ($deleted > 0) {
            return ['success' => true, 'message' => 'Public property removed'];
        }

        return ['success' => true, 'message' => 'Verse was not public'];
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
     *     description="为 Verse 添加标签",
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
     * @param string $alias 表别名，默认为 'verse_tags'
     */
    private function applyTagsFilter($query, $alias = 'verse_tags')
    {
        $tags = Yii::$app->request->get('tags');
        if ($tags) {
            $tagsArray = array_map('intval', explode(',', $tags));
            if (!empty($tagsArray)) {
                $query->innerJoin("verse_tags AS {$alias}", "{$alias}.verse_id = verse.id")
                    ->andWhere(['in', "{$alias}.tags_id", $tagsArray])
                    ->groupBy('verse.id');
            }
        }
    }
}
