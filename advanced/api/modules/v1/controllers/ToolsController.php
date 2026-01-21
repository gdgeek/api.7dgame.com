<?php

namespace api\modules\v1\controllers;
use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use Yii;
use api\modules\v1\models\UserLinked;
use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tools",
 *     description="工具类接口"
 * )
 */
class ToolsController extends \yii\rest\Controller
{

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
        // re-add authentication filter

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }

   /**
     * @OA\Get(
     *     path="/v1/tools/user-linked",
     *     summary="用户关联",
     *     description="生成用户关联密钥",
     *     tags={"Tools"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="关联成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="user-linked"),
     *             @OA\Property(property="key", type="string", description="关联密钥")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
   public function actionUserLinked(){

    //把 Yii::$app->user->identity 转换成 User 类型

        $identity = Yii::$app->user->identity;
        if ($identity instanceof User) {
            $user = $identity;
            // 现在 $user 是 User 类型
        } else {
            throw new \yii\web\UnauthorizedHttpException('Invalid user identity');
        }
        if(!$user){
            throw new BadRequestHttpException("no user");
        }
        $linked = UserLinked::find()->where(['user_id' => $user->id])->one();
        
        if(!$linked){
            $linked = new UserLinked();
            $linked->user_id = $user->id;
        }
        $token = $user->getRefreshToken()->one();
        if(!$token){
            $token = $user->token();
        }
        $linked->key = $token->key;
        if(!$linked->validate()){
            throw new BadRequestHttpException("validate error");
        }
        if(!$linked->save()){
            throw new BadRequestHttpException("save error");
        }
      
        return ['success' => true, 'message' => "user-linked", 'key'=> $linked->key];
       
    }
}
