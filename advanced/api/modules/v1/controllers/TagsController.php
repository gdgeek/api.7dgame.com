<?php
namespace api\modules\v1\controllers;

use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use Yii;
use OpenApi\Annotations as OA;

use api\modules\v1\models\TagsSearch;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="标签管理接口"
 * )
 */
class TagsController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Tags';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET'],
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
     //  $actions = parent::actions();
        return [];
    }
   
    /**
     * @OA\Get(
     *     path="/v1/tags",
     *     summary="获取标签列表",
     *     description="获取所有标签列表",
     *     tags={"Tags"},
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
     *         description="标签列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="标签ID", example=1),
     *                 @OA\Property(property="name", type="string", description="标签名称", example="教育"),
     *                 @OA\Property(property="key", type="string", description="标签键", example="education"),
     *                 @OA\Property(property="type", type="string", description="标签类型", example="category")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {

        $searchModel = new TagsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $dataProvider;
    }

}
