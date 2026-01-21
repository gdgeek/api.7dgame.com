<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\Wechat;
use api\modules\v1\models\User;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Wechat",
 *     description="微信相关接口"
 * )
 */
class WechatController extends \yii\rest\Controller
{

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }

    /**
     * @OA\Post(
     *     path="/v1/wechat/login",
     *     summary="微信登录",
     *     description="使用微信 token 进行登录",
     *     tags={"Wechat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", description="微信 token", example="wx_token_123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="login"),
     *             @OA\Property(property="token", type="string", description="JWT Token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误 - token 缺失或微信账号不存在")
     * )
     */
    public function actionLogin(){
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $wechat = Wechat::findOne(['token'=>$token]);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");

        }
        if($wechat->user){
            return ['success' => true, 'message' => "login", 'token'=> $wechat->user->token()];
        }else{
            throw new BadRequestHttpException('Login failed');
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/wechat/register",
     *     summary="微信注册",
     *     description="使用微信 token 注册新用户",
     *     tags={"Wechat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "username", "password"},
     *             @OA\Property(property="token", type="string", description="微信 token", example="wx_token_123456"),
     *             @OA\Property(property="username", type="string", description="用户名", example="newuser"),
     *             @OA\Property(property="password", type="string", description="密码", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="注册成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="register"),
     *             @OA\Property(property="uid", type="integer", description="用户ID"),
     *             @OA\Property(property="token", type="string", description="JWT Token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误 - 参数缺失或已注册")
     * )
     */
    public function actionRegister(){
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $username = Yii::$app->request->post("username");
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        $password = Yii::$app->request->post("password");
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        $wechat = Wechat::findOne(['token'=>$token]);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");
        }
        if($wechat->user){

            throw new BadRequestHttpException("already registered,". $wechat->user->id);
        }else{
            $user = User::create($username, $password);
            if(!$user->validate()){
                throw new BadRequestHttpException(json_encode($user->errors));
            }
            $user->save();
            $user->addRoles(["user"]);
           
            $wechat->user_id = $user->id;
           if(!$wechat->validate() ){
                throw new BadRequestHttpException(json_encode($wechat->errors, true));
            }
            $wechat->save();
            return ['success' => true, 'message' => "register", 'uid'=>$user->id, 'token'=> $user->token()];
           
        }
    }
  


}