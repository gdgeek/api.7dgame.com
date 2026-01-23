<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MetaSearch;
use api\modules\v1\models\data\MetaCodeTool;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

use yii\base\Exception;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Meta",
 *     description="Meta 元数据管理接口"
 * )
 */
class MetaController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\Meta';
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
    
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/v1/meta/{id}",
     *     summary="获取 Meta 详情",
     *     description="根据ID获取 Meta 元数据详情",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Meta 详情",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionView($id)
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id' => $id, 'prefab' => 0]);
        return $dataProvider->query->one();
    }

    /**
     * @OA\Delete(
     *     path="/v1/meta/{id}",
     *     summary="删除 Meta",
     *     description="删除指定的 Meta 元数据（预制件不可删除）",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="删除成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="禁止删除预制件"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionDelete($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        if ($model->prefab == 1) {
            throw new \yii\web\ForbiddenHttpException('You can not delete this item');
        }
        $model->delete();
        return $model;
    }

    /**
     * @OA\Put(
     *     path="/v1/meta/{id}",
     *     summary="更新 Meta",
     *     description="更新指定的 Meta 元数据",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Meta 名称"),
     *             @OA\Property(property="info", type="string", description="Meta 信息（JSON）"),
     *             @OA\Property(property="resource_id", type="integer", description="资源ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionUpdate($id)
    {
        $model = \api\modules\v1\models\Meta::findOne($id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 0;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/meta",
     *     summary="创建 Meta",
     *     description="创建新的 Meta 元数据",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Meta 名称", example="My Meta"),
     *             @OA\Property(property="info", type="string", description="Meta 信息（JSON）", example="{}"),
     *             @OA\Property(property="resource_id", type="integer", description="资源ID", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="创建成功",
     *         @OA\JsonContent(ref="#/components/schemas/Meta")
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionCreate()
    {
        $model = new \api\modules\v1\models\Meta();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->prefab = 0;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/meta",
     *     summary="获取 Meta 列表",
     *     description="获取当前用户的 Meta 元数据列表",
     *     tags={"Meta"},
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
     *         description="Meta 列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Meta")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionIndex()
    {
        $searchModel = new MetaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id, 'prefab' => 0]);
        return $dataProvider;
    }
    
    /**
     * @OA\Put(
     *     path="/v1/meta/{id}/code",
     *     summary="更新 Meta 代码",
     *     description="更新 Meta 的代码（Blockly、Lua、JavaScript）",
     *     tags={"Meta"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Meta ID",
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
     *     @OA\Response(response=404, description="Meta 不存在")
     * )
     */
    public function actionUpdateCode($id){
        
        
        $post = Yii::$app->request->post();
        $model = new MetaCodeTool($id);
        $post = Yii::$app->request->post();
        
        
         $post = Yii::$app->request->post();
        $model->load($post, '');

       // throw new Exception(json_encode($model));
        if ($model->validate()) {
            $model->save();
        }else{
            throw new Exception(json_encode($model->errors), 400);
        }
        return $model;

        
    }
    
}
