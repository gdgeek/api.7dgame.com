<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Group;
use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Group",
 *     description="群组管理接口"
 * )
 */
class GroupController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Group';

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

    /**
     * Join a group
     * @param int|null $id Group ID (from URL path, e.g., /group/22/join)
     * @return \api\modules\v1\models\GroupUser
     * 
     * @OA\Post(
     *     path="/v1/group/{id}/join",
     *     summary="加入群组",
     *     description="加入指定的群组",
     *     tags={"Group"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="群组ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="加入成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="关联ID"),
     *             @OA\Property(property="user_id", type="integer", description="用户ID"),
     *             @OA\Property(property="group_id", type="integer", description="群组ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="群组不存在"),
     *     @OA\Response(response=409, description="已经加入该群组")
     * )
     */
    public function actionJoin($id = null)
    {
        $userId = Yii::$app->user->id;

        // 优先使用 URL 路径中的 id，其次使用 POST body 中的 group_id
        $groupId = $id ?? Yii::$app->request->post('group_id');

        if (!$groupId) {
            throw new \yii\web\BadRequestHttpException('Group ID is required.');
        }

        $group = Group::findOne($groupId);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }

        // 检查是否已经加入
        $exists = \api\modules\v1\models\GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $groupId])
            ->exists();

        if ($exists) {
            throw new \yii\web\ConflictHttpException('You have already joined this group.');
        }

        $model = new \api\modules\v1\models\GroupUser();
        $model->user_id = $userId;
        $model->group_id = $groupId;
        if (!$model->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to join group.');
        }

        return $model;
    }

    /**
     * Leave a group
     * @param int $id Group ID
     * @return array
     * 
     * @OA\Post(
     *     path="/v1/group/{id}/leave",
     *     summary="离开群组",
     *     description="离开指定的群组（创建者不能离开）",
     *     tags={"Group"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="群组ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="离开成功"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="创建者不能离开群组"),
     *     @OA\Response(response=404, description="群组不存在或未加入该群组")
     * )
     */
    public function actionLeave($id)
    {
        $userId = Yii::$app->user->id;

        $group = Group::findOne($id);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }

        // 创建者不能退出自己创建的小组
        if ($group->user_id == $userId) {
            throw new \yii\web\ForbiddenHttpException('Group creator cannot leave the group.');
        }

        $model = \api\modules\v1\models\GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $id])
            ->one();

        if (!$model) {
            throw new \yii\web\NotFoundHttpException('You are not a member of this group.');
        }

        if (!$model->delete()) {
            throw new \yii\web\ServerErrorHttpException('Failed to leave group.');
        }

        Yii::$app->response->statusCode = 204;
        return [];
    }

    /**
     * Get verses for a group
     * @param int $id Group ID
     * @return array
     * 
     * @OA\Get(
     *     path="/v1/group/{id}/verses",
     *     summary="获取群组的 Verses",
     *     description="获取指定群组下的所有 Verse 列表",
     *     tags={"Group"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="群组ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
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
     *                 @OA\Property(property="uuid", type="string", description="Verse UUID")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="群组不存在")
     * )
     */
    public function actionGetVerses($id)
    {

        /**

        'verse.image,verse.author',
        这个调用，需要
         */

        $group = Group::findOne($id);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }

        $searchModel = new \api\modules\v1\models\VerseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // 使用 leftJoin 手动关联 group_verse 表
        // 这样 Verse 模型不需要定义任何与 Group 相关的关联
        // Group 单向依赖 Verse

        if ($dataProvider->query instanceof \yii\db\ActiveQuery) {
            $dataProvider->query
                ->select('verse.*')
                ->leftJoin('group_verse', 'group_verse.verse_id = verse.id')
                ->andWhere(['group_verse.group_id' => $id]);
        }


        return $dataProvider;
    }

    /**
     * Create a verse for a group
     * 在指定 Group 下创建一个新的 Verse，并建立 GroupVerse 关联
     * 
     * @param int $id Group ID
     * @return \api\modules\v1\models\Verse
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     * 
     * @OA\Post(
     *     path="/v1/group/{id}/verse",
     *     summary="创建 Verse",
     *     description="在群组下创建新的 Verse",
     *     tags={"Group"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="群组ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Verse 名称", example="My Verse"),
     *             @OA\Property(property="description", type="string", description="描述"),
     *             @OA\Property(property="data", type="string", description="Verse 数据（JSON）")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="Verse ID"),
     *             @OA\Property(property="name", type="string", description="Verse 名称"),
     *             @OA\Property(property="uuid", type="string", description="Verse UUID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="群组不存在")
     * )
     */
    public function actionCreateVerse($id)
    {
        $group = Group::findOne($id);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 创建 Verse
            $verse = new \api\modules\v1\models\Verse();
            $verse->load(Yii::$app->request->post(), '');

            if (!$verse->save()) {
                throw new \yii\web\ServerErrorHttpException('Failed to create verse: ' . json_encode($verse->errors));
            }

            // 创建 GroupVerse 关联
            $groupVerse = new \api\modules\v1\models\GroupVerse();
            $groupVerse->group_id = $id;
            $groupVerse->verse_id = $verse->id;

            if (!$groupVerse->save()) {
                throw new \yii\web\ServerErrorHttpException('Failed to create group-verse relation: ' . json_encode($groupVerse->errors));
            }

            $transaction->commit();

            return $verse;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a verse from a group
     * 删除 Group 下的 Verse 关联，并删除 Verse 本身
     * 
     * @param int $id Group ID
     * @param int $verseId Verse ID
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     * 
     * @OA\Delete(
     *     path="/v1/group/{id}/verse/{verseId}",
     *     summary="删除 Verse",
     *     description="从群组中删除 Verse（如果没有其他群组引用，则删除 Verse 本身）",
     *     tags={"Group"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="群组ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="verseId",
     *         in="path",
     *         description="Verse ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="删除成功"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="群组或 Verse 不存在")
     * )
     */
    public function actionDeleteVerse($id, $verseId)
    {
        $group = Group::findOne($id);
        if (!$group) {
            throw new \yii\web\NotFoundHttpException('Group not found.');
        }

        // 查找 GroupVerse 关联
        $groupVerse = \api\modules\v1\models\GroupVerse::findOne([
            'group_id' => $id,
            'verse_id' => $verseId,
        ]);

        if (!$groupVerse) {
            throw new \yii\web\NotFoundHttpException('Verse not found in this group.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 删除 GroupVerse 关联
            if (!$groupVerse->delete()) {
                throw new \yii\web\ServerErrorHttpException('Failed to delete group-verse relation.');
            }

            // 检查该 Verse 是否还被其他 Group 引用
            $otherLinks = \api\modules\v1\models\GroupVerse::find()
                ->where(['verse_id' => $verseId])
                ->count();

            // 如果没有其他引用，删除 Verse 本身
            if ($otherLinks == 0) {
                $verse = \api\modules\v1\models\Verse::findOne($verseId);
                if ($verse && !$verse->delete()) {
                    throw new \yii\web\ServerErrorHttpException('Failed to delete verse.');
                }
            }

            $transaction->commit();

            Yii::$app->response->statusCode = 204;
            return null;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
