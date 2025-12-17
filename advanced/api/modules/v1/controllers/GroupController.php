<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Group;
use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;

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
        $dataProvider->query
            ->select('verse.*')
            ->leftJoin('group_verse', 'group_verse.verse_id = verse.id')
            ->andWhere(['group_verse.group_id' => $id]);

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
