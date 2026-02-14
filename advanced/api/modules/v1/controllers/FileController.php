<?php
namespace api\modules\v1\controllers;

use common\components\security\RateLimitBehavior;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use OpenApi\Annotations as OA;

/**
 * FileController — 文件记录管理控制器（纯数据库 CRUD）
 *
 * 重要：本控制器不负责文件上传！
 * 本平台所有文件上传都通过腾讯云 COS 直传完成，流程如下：
 *   1. 前端通过 /v1/tencent-clouds/token 获取 COS 临时密钥
 *   2. 前端直传文件到 COS
 *   3. 前端调用 POST /v1/files 创建 File 数据库记录（传 url/filename/key）
 *
 * 本控制器仅管理 file 表的数据库记录（增删改查）。
 *
 * @OA\Tag(
 *     name="File",
 *     description="文件记录管理接口（数据库 CRUD，非文件上传）"
 * )
 */
class FileController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\File';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

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
        $behaviors['rateLimiter'] = [
            'class' => RateLimitBehavior::class,
            'rateLimiter' => 'rateLimiter',
            'defaultStrategy' => 'ip',
        ];
        return $behaviors;
    }

    /**
     * @OA\Get(
     *     path="/v1/file",
     *     summary="获取文件列表",
     *     description="获取当前用户的文件列表",
     *     tags={"File"},
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
     *         description="文件列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/File")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => false,
        ]);
        return $activeData;
    }
}
