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
use api\modules\v1\RefreshToken;
use api\modules\v1\models\UserLinked;
use api\modules\v1\services\IdentityService;
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
    private ?IdentityService $identityService = null;

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

    protected function identityService(): IdentityService
    {
        if ($this->identityService === null) {
            $this->identityService = new IdentityService();
        }

        return $this->identityService;
    }

    protected function requestContext(): array
    {
        return $this->identityService()->sessionService()->contextFromRequest(Yii::$app->request);
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

        $user = $this->currentUser();
        $linked = UserLinked::find()->where(['user_id' => $user->id])->one();
        
        if(!$linked){
            $linked = new UserLinked();
            $linked->user_id = $user->id;
        }
        $loginCode = Yii::$app->security->generateRandomString(64);
        $linked->key = RefreshToken::hashToken($loginCode);
        if(!$linked->validate()){
            throw new BadRequestHttpException("validate error");
        }
        if(!$linked->save()){
            throw new BadRequestHttpException("save error");
        }

        $expiresAt = time() + UserLinked::LOGIN_CODE_TTL_SECONDS;

        return [
            'success' => true,
            'message' => "user-linked",
            'key'=> $loginCode,
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresAt - time()),
        ];
       
    }

    /**
     * @OA\Get(
     *     path="/v1/tools/user-linked/status",
     *     summary="用户关联密钥状态",
     *     description="检查当前二维码登录码是否仍然有效，不会生成新的登录码",
     *     tags={"Tools"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         required=true,
     *         description="当前二维码中的登录码，可带 web_ 前缀",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="检查成功"),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionUserLinkedStatus()
    {
        $user = $this->currentUser();
        $key = $this->normalizeLinkedKey((string)Yii::$app->request->get('key', ''));
        if ($key === '') {
            throw new BadRequestHttpException("key is required");
        }

        $linked = UserLinked::find()->where(['user_id' => $user->id])->one();
        $active = false;
        $reason = 'not_found';
        $expiresAt = null;

        if ($linked instanceof UserLinked && hash_equals((string)$linked->key, RefreshToken::hashToken($key))) {
            $expiresAt = $linked->loginCodeExpiresAt();
            if ($linked->isLoginCodeExpired()) {
                $reason = 'expired';
            } else {
                $active = true;
                $reason = 'active';
            }
        }

        $response = [
            'success' => true,
            'message' => 'user-linked-status',
            'active' => $active,
            'reason' => $reason,
        ];

        if ($expiresAt !== null) {
            $response['expires_at'] = $expiresAt;
            $response['expires_in'] = max(0, $expiresAt - time());
        }

        return $response;
    }

    private function currentUser(): User
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof User) {
            return $identity;
        }

        throw new \yii\web\UnauthorizedHttpException('Invalid user identity');
    }

    private function normalizeLinkedKey(string $key): string
    {
        $key = trim($key);

        if (preg_match('/(?:^|[?&])web_([^&#\s]+)/', $key, $matches) === 1) {
            return $matches[1];
        }

        if (strpos($key, 'web_') === 0) {
            return substr($key, 4);
        }

        return $key;
    }
}
