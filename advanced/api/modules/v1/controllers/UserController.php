<?php

namespace api\modules\v1\controllers;

use api\common\models\UserDataForm;
use api\modules\v1\models\User;
use api\modules\v1\models\UserInfo;

use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use mdm\admin\components\Configs;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="User",
 *     description="用户管理相关接口"
 * )
 */
class UserController extends \yii\rest\Controller
{
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
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        // re-add authentication filter
/*
        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
*/
        return $behaviors;
    }
    /*
    private function getAssignments($userId)
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($userId);
        $ret = [];
        foreach ($assignments as $key => $value) {
            $ret[] = $value->roleName;

        }
        return $ret;
    }*/
    private function getUserData()
    {
        $user =  Yii::$app->user->identity;
      
        return [
            'id' => $user->id,
            'userData' => $user->data,
            'userInfo' => $user->userInfo,
            'roles' => $user->roles,
        ];

    }
    public function actionCreation()
    {
        $creation = new UserCreation();
        return $creation;
    }
    /**
     * 更新用户数据
     * 
     * @OA\Post(
     *     path="/v1/user/update",
     *     summary="更新用户数据",
     *     tags={"User"},
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nickname", type="string", description="昵称"),
     *             @OA\Property(property="avatar", type="string", description="头像URL")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="更新成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ok"),
     *             @OA\Property(property="data", type="object", description="用户数据")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionUpdate()
    {
        $model = new UserDataForm(Yii::$app->user->identity);
        $post = Yii::$app->request->post();
        $model->load($post, '');
       // return $model;
        if ($model->validate()) {
            $model->save();
            return ['success' => true, 'message'=>'ok', 'data' => $this->getUserData()];
          
        } else {
        
            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }

        }
    }
    
    /**
     * 获取用户信息
     * 
     * @OA\Get(
     *     path="/v1/user/info",
     *     summary="获取用户信息",
     *     tags={"User"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="用户信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ok"),
     *             @OA\Property(property="data", type="object", description="用户数据",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="userData", type="object"),
     *                 @OA\Property(property="userInfo", type="object"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionInfo()
    {   
        //$id = User::tokenToId("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgxIiwiaWF0IjoxNzM5NjkxNzkzLjU1MTg1LCJuYmYiOjE3Mzk2OTE3OTMuNTUxODUsImV4cCI6MTczOTcwMjU5My41NTE4NSwidWlkIjo1Mzl9.bIiR__RaesmVP4YCRE-eNL87TO4JFKDosAJmj-kmOPc");

      
        //throw new Exception(json_encode($id));
        return ['success' => true, 'message'=>'ok', 'data' => $this->getUserData()];
    }

}
