<?php
namespace api\modules\v1\controllers;
use api\modules\v1\models\Tags;
use api\modules\v1\models\VerseSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use api\modules\v1\models\data\VerseCodeTool;
use OpenApi\Annotations as OA;

use yii\base\Exception;

/**
 * @OA\Tag(
 *     name="VerseTags",
 *     description="Verse 标签管理接口"
 * )
 */
class VerseTagsController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\VerseTags';
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
        //  $actions = parent::actions();
        return [];
    }

    /**
     * @OA\Post(
     *     path="/v1/verse-tags",
     *     summary="添加 Verse 标签",
     *     description="为 Verse 添加标签",
     *     tags={"VerseTags"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verse_id", "tags_id"},
     *             @OA\Property(property="verse_id", type="integer", description="Verse ID", example=1),
     *             @OA\Property(property="tags_id", type="integer", description="标签ID", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="添加成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=403, description="无权限添加此标签")
     * )
     */
    //添加标签
    public function actionCreate(){
        $verse_id = Yii::$app->request->post('verse_id');
        $tags_id = Yii::$app->request->post('tags_id');
        $tags = Tags::findOne($tags_id);
        if(!$tags) {
            throw new Exception('Tags not found');
        }
        $permission = Yii::$app->user->can('root')||  Yii::$app->user->can('admin');
        if($tags->type == 'Status' && !$permission){
            throw new Exception('You do not have permission to add this tag');
        }

        $model = new \api\modules\v1\models\VerseTags();
        $model->verse_id = $verse_id;
        $model->tags_id = $tags_id;
        
        if($model->save()){
            return [
                'success' => true,
                'message' => 'success'
            ];
        }else {
           
            throw new Exception(json_encode($model->getErrors()));
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/verse-tags/remove",
     *     summary="移除 Verse 标签",
     *     description="从 Verse 移除标签",
     *     tags={"VerseTags"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verse_id", "tags_id"},
     *             @OA\Property(property="verse_id", type="integer", description="Verse ID", example=1),
     *             @OA\Property(property="tags_id", type="integer", description="标签ID", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="移除成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权"),
     *     @OA\Response(response=404, description="标签关联不存在")
     * )
     */
    public function actionRemove(){
        $verse_id = Yii::$app->request->post('verse_id');
        $tags_id = Yii::$app->request->post('tags_id');
        $model = \api\modules\v1\models\VerseTags::find()->where(['verse_id' => $verse_id, 'tags_id' => $tags_id])->one();
        if($model){
            $model->delete();
            return [
                'success' => true,
                'message' => 'success'
            ];
        }
        throw new Exception('VerseTags not found');
    }
}
