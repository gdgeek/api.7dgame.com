<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use api\modules\v1\models\EduClassSearch;
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

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);
    }

    /**
     * Get all classes where current user is a student
     * @return array
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
