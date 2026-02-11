<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Property;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseSearch;
use api\modules\v1\models\data\VerseCodeTool;
use bizley\jwt\JwtHttpBearerAuth;
use yii\db\ActiveQuery;
use mdm\admin\components\AccessControl;
use Yii;
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
        unset($actions['index']);
        return $actions;
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
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);

        // 处理标签过滤
        $this->applyTagsFilter($dataProvider->query);

        return $dataProvider;
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
        $post = Yii::$app->request->post();
        $model = new VerseCodeTool($id);
        $model->load($post, '');
        if ($model->validate()) {
            $model->save();
        } else {
            throw new BadRequestHttpException(json_encode($model->errors));
        }
        return $model;
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
     *             @OA\Property(property="data", type="string", description="快照数据（JSON）")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Verse 不存在")
     * )
     */
    public function actionTakePhoto($id)
    {
        $snapshot = Snapshot::CreateById($id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image']);
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
