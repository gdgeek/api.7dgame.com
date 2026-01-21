<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use api\modules\v1\models\User;
use api\modules\v1\models\EduClassSearch;
use api\modules\v1\models\EduTeacher;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="EduClass",
 *     description="班级管理"
 * )
 */
class EduClassController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\EduClass';

    /**
     * @OA\Get(
     *     path="/edu-class",
     *     tags={"EduClass"},
     *     summary="Get class list",
     *     description="Get list of classes",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="school_id",
     *         in="query",
     *         description="Filter by school ID (required)",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per-page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EduClass")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * 
     * @OA\Post(
     *     path="/edu-class",
     *     tags={"EduClass"},
     *     summary="Create class",
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EduClass")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/EduClass")
     *     )
     * )
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * List edu classes with search/filter support
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new EduClassSearch();
        $dataProvider = $searchModel->search($params);

        return $dataProvider;
    }

    /**
     * Get all classes where current user is a teacher
     * @return array
     * 
     * @OA\Get(
     *     path="/v1/edu-class/by-teacher",
     *     summary="按教师查询班级",
     *     description="获取当前用户作为教师的所有班级",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="班级列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EduClass")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionByTeacher()
    {
        $userId = Yii::$app->user->id;

        $classes = \api\modules\v1\models\EduClass::find()
            ->innerJoin('edu_teacher', 'edu_teacher.class_id = edu_class.id')
            ->where(['edu_teacher.user_id' => $userId])
            ->all();

        return $classes;
    }

    /**
     * Get all classes where current user is a teacher (dataProvider)
     * GET /v1/edu-class/teacher-me
     *
     * @return ActiveDataProvider
     * 
     * @OA\Get(
     *     path="/v1/edu-class/teacher-me",
     *     summary="我的教师班级",
     *     description="获取当前用户作为教师的班级列表（分页）",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="school_id",
     *         in="query",
     *         description="学校ID（可选）",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="expand",
     *         in="query",
     *         description="扩展字段（逗号分隔，如：school,image）",
     *         required=false,
     *         @OA\Schema(type="string", example="school,image")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="班级列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EduClass")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionTeacherMe()
    {
        $userId = (int)Yii::$app->user->id;
        if (!$userId) {
            throw new BadRequestHttpException('Unauthorized');
        }

        $query = \api\modules\v1\models\EduClass::find()
            ->alias('c')
            ->select('c.*')
            ->innerJoin('edu_teacher t', 't.class_id = c.id')
            ->andWhere(['t.user_id' => $userId])
            ->distinct();

        // 可选：按 school_id 过滤
        $schoolId = Yii::$app->request->get('school_id');
        if ($schoolId !== null && $schoolId !== '') {
            $query->andWhere(['c.school_id' => (int)$schoolId]);
        }

        // 按 expand 预加载关联，避免 N+1
        $expandRaw = (string)Yii::$app->request->get('expand', '');
        $expand = array_filter(array_map('trim', explode(',', $expandRaw)));

        $with = [];
        if (in_array('school', $expand, true)) {
            $with[] = 'school';
        }
        if (in_array('image', $expand, true)) {
            $with[] = 'image';
        }
        if (!empty($with)) {
            $query->with($with);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);
    }

    /**
     * Add a teacher to a class
     * POST /v1/edu-class/{id}/teacher
     *
     * Body: {"user_id": 123}，如果不传 user_id，默认添加当前登录用户
     *
     * @param int $id Class ID
     * @return EduTeacher
     * 
     * @OA\Post(
     *     path="/v1/edu-class/{id}/teacher",
     *     summary="添加教师",
     *     description="为班级添加教师",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="班级ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", description="教师用户ID（可选，默认当前用户）", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="添加成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="关联ID"),
     *             @OA\Property(property="class_id", type="integer", description="班级ID"),
     *             @OA\Property(property="user_id", type="integer", description="教师用户ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="班级不存在"),
     *     @OA\Response(response=409, description="教师已存在")
     * )
     */
    public function actionTeacher($id)
    {
        $class = $this->findModel($id);

        $userId = (int)Yii::$app->request->post('user_id', 0);
        if ($userId <= 0) {
            $userId = (int)Yii::$app->user->id;
        }
        if ($userId <= 0) {
            throw new BadRequestHttpException('user_id is required');
        }

        $exists = EduTeacher::find()
            ->andWhere(['class_id' => (int)$class->id, 'user_id' => $userId])
            ->exists();
        if ($exists) {
            throw new ConflictHttpException('This teacher is already in this class');
        }

        $model = new EduTeacher();
        $model->class_id = (int)$class->id;
        $model->user_id = $userId;

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        // 让 Yii2 统一输出 422 + errors
        return $model;
    }

    /**
     * Remove a teacher from a class
     * DELETE /v1/edu-class/{id}/teacher
     *
     * 支持：?user_id=123 或 Body: {"user_id":123}；如果不传 user_id，默认移除当前登录用户
     *
     * @param int $id Class ID
     * @return null
     * 
     * @OA\Delete(
     *     path="/v1/edu-class/{id}/teacher",
     *     summary="移除教师",
     *     description="从班级移除教师",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="班级ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="教师用户ID（可选，默认当前用户）",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="移除成功"),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="班级或教师不存在")
     * )
     */
    public function actionRemoveTeacher($id)
    {
        $class = $this->findModel($id);

        $userId = (int)Yii::$app->request->get('user_id', 0);
        if ($userId <= 0) {
            $userId = (int)Yii::$app->request->post('user_id', 0);
        }
        if ($userId <= 0) {
            $userId = (int)Yii::$app->user->id;
        }
        if ($userId <= 0) {
            throw new BadRequestHttpException('user_id is required');
        }

        $model = EduTeacher::findOne(['class_id' => (int)$class->id, 'user_id' => $userId]);
        if (!$model) {
            throw new NotFoundHttpException('This teacher is not in this class');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->response->statusCode = 204;
        return null;
    }

    /**
     * Get all classes where current user is a student
     * @return array
     * 
     * @OA\Get(
     *     path="/v1/edu-class/by-student",
     *     summary="按学生查询班级",
     *     description="获取当前用户作为学生的所有班级",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="班级列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EduClass")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionByStudent()
    {
        $userId = Yii::$app->user->id;

        $classes = \api\modules\v1\models\EduClass::find()
            ->innerJoin('edu_student', 'edu_student.class_id = edu_class.id')
            ->where(['edu_student.user_id' => $userId])
            ->all();

        return $classes;
    }

    /**
     * Get groups for a class
     * @param int $id Class ID
     * @return \yii\data\ActiveDataProvider
     * 
     * @OA\Get(
     *     path="/v1/edu-class/{id}/groups",
     *     summary="获取班级群组",
     *     description="获取指定班级的所有群组",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="班级ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="群组列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="群组ID"),
     *                 @OA\Property(property="name", type="string", description="群组名称"),
     *                 @OA\Property(property="description", type="string", description="群组描述")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionGetGroups($id)
    {


        $searchModel = new \api\modules\v1\models\GroupSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // 使用手写 leftJoin，Group 模型不需要定义 getEduClassGroups 关联
        // EduClassGroup 单向依赖 Group 和 EduClass
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query
            ->select('`group`.*')
            ->leftJoin('edu_class_group', 'edu_class_group.group_id = `group`.id')
            ->andWhere(['edu_class_group.class_id' => $id]);
            
        return $dataProvider;
    }

    /**
     * Create a group for a class
     * @param int $id Class ID
     * @return \api\modules\v1\models\Group
     * 
     * @OA\Post(
     *     path="/v1/edu-class/{id}/group",
     *     summary="创建班级群组",
     *     description="为班级创建新的群组",
     *     tags={"EduClass"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="班级ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="群组名称", example="小组1"),
     *             @OA\Property(property="description", type="string", description="群组描述")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="创建成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="群组ID"),
     *             @OA\Property(property="name", type="string", description="群组名称"),
     *             @OA\Property(property="user_id", type="integer", description="创建者ID")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="班级不存在")
     * )
     */
    public function actionCreateGroup($id)
    {
        $class = $this->findModel($id);

        $group = new \api\modules\v1\models\Group();
        $group->load(Yii::$app->request->post(), '');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($group->save()) {
                // Link to class
                $link = new \api\modules\v1\models\EduClassGroup();
                $link->class_id = $class->id;
                $link->group_id = $group->id;
                if (!$link->save()) {
                    throw new \Exception('Failed to save link.');
                }

                // Add creator to group
                $groupUser = new \api\modules\v1\models\GroupUser();
                $groupUser->group_id = $group->id;
                $groupUser->user_id = Yii::$app->user->id;
                if (!$groupUser->save()) {
                    throw new \Exception('Failed to add user to group.');
                }

                $transaction->commit();
                return $group;
            } elseif (!$group->hasErrors()) {
                throw new \yii\web\ServerErrorHttpException('Failed to create the object for unknown reason.');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $group;
    }

    /**
     * Finds the EduClass model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return \api\modules\v1\models\EduClass the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \api\modules\v1\models\EduClass::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
    }

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

}
