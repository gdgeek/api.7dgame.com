<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\File;
use common\components\security\RateLimitBehavior;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

     
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

    public function actionCreate()
    {
        $body = Yii::$app->request->bodyParams;
        $key = trim((string)($body['key'] ?? ''));
        if ($key !== '') {
            $existingFile = File::find()
                ->where([
                    'user_id' => Yii::$app->user->id,
                    'key' => $key,
                ])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            if ($existingFile) {
                Yii::$app->response->statusCode = 200;
                return $existingFile;
            }
        }

        $model = new File();
        $model->load($body, '');
        $model->user_id = (int) Yii::$app->user->id;
        $this->checkAccess('create', $model);

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        if (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        Yii::$app->response->statusCode = 422;
        return $model;
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
