<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use api\modules\v1\models\EduSchool;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="EduSchool",
 *     description="学校管理接口"
 * )
 */
class EduSchoolController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\EduSchool';
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

    /**
     * @OA\Get(
     *     path="/v1/edu-school/principal",
     *     summary="获取校长的学校列表",
     *     description="获取当前用户作为校长的学校列表",
     *     tags={"EduSchool"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string", example="实验小学")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="排序字段（-表示降序）",
     *         required=false,
     *         @OA\Schema(type="string", example="-created_at")
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
     *         description="学校列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="学校ID", example=1),
     *                 @OA\Property(property="name", type="string", description="学校名称", example="实验小学"),
     *                 @OA\Property(property="principal_id", type="integer", description="校长用户ID", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="创建时间"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="更新时间")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    /**
     * 获取当前用户作为校长的学校列表
     * GET /edu-school/principal
     * @return ActiveDataProvider
     */
    public function actionPrincipal()
    {
        $userId = Yii::$app->user->id;
        
        $query = EduSchool::find()
            ->where(['principal_id' => $userId]);
        
        // 搜索
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['like', 'name', $search]);
        }
        
        // 排序
        $sort = Yii::$app->request->get('sort', '-created_at');
        
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $this->parseSortParam($sort),
                'attributes' => ['id', 'name', 'created_at', 'updated_at'],
            ],
        ]);
    }

    /**
     * 解析排序参数
     * @param string $sort 如 "-created_at" 或 "name"
     * @return array
     */
    private function parseSortParam($sort)
    {
        if (empty($sort)) {
            return ['created_at' => SORT_DESC];
        }
        
        if ($sort[0] === '-') {
            return [substr($sort, 1) => SORT_DESC];
        }
        
        return [$sort => SORT_ASC];
    }
    
}
