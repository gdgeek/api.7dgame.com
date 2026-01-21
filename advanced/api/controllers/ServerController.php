<?php

namespace api\controllers;

use api\common\models\BindEmailForm;
use api\common\models\ResetPasswordForm;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use OpenApi\Annotations as OA;
use api\modules\v1\models\User;

/**
 * @OA\Tag(
 *     name="Server",
 *     description="服务器管理相关接口"
 * )
 */
class ServerController extends \yii\rest\Controller

{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
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
            'class' => CompositeAuth::className(),
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
     * 获取当前用户信息
     * 
     * @OA\Get(
     *     path="/server/user",
     *     summary="获取当前用户信息",
     *     tags={"Server"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="用户信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="data", type="object", description="用户数据")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionUser()
    {
        $user = new \stdClass();

        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User) {
            throw new \yii\web\UnauthorizedHttpException('未授权');
        }
        $user->username = $identity->username;
        $user->data = $identity->getData();
        //$user->code = 20000;
        return $user;
    }
    /**
     * 用户登出
     * 
     * @OA\Post(
     *     path="/server/logout",
     *     summary="用户登出",
     *     tags={"Server"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="登出成功",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        $ret = new \stdClass();
        // $ret->code = 20000;
        return $ret;
    }
    public function actionBindEmail()
    {
        $model = new BindEmailForm(Yii::$app->user->identity);
        $post = Yii::$app->request->post();
        if ($model->load($post, '')) {

            if ($model->email == Yii::$app->user->identity->email) {
                throw new Exception("重复绑定", 400);
            }
        } else {
            throw new Exception("缺少数据", 400);
        }
          $identity = Yii::$app->user->identity;
        if (!$identity instanceof User) {
            throw new \yii\web\UnauthorizedHttpException('未授权');
        }

        $user = $identity;
        $user->generateEmailVerificationTokenWithEmail($model->email);

        if ($model->validate()) {
            $model->sendEmail();
            return [
                'data' => Yii::$app->user->identity->getData(),
                'code' => 20000,
            ];
        } else {
            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }
        }
    }
    public function actionResetPassword()
    {
        $model = new ResetPasswordForm(Yii::$app->user->identity);
        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->validate()) {
            $model->resetPassword();
            return [
                'data' => '密码已经修改完成。',
                'code' => 20000,
            ];
        } else {
            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }
        }
    }
    /**
     * 刷新访问令牌
     * 
     * @OA\Post(
     *     path="/server/token",
     *     summary="刷新访问令牌",
     *     tags={"Server"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="新的访问令牌",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="新的 JWT Token")
     *         )
     *     ),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionToken()
    {
        $ret = new \stdClass();
        $ret->token = Yii::$app->user->identity->generateAccessToken();
        return $ret;
    }

    public function actionMenu()
    {
        $callback = function ($menu) {
            $data = json_decode($menu['data'], true);
            $items = $menu['children'];
            $return = [
                'label' => $menu['name'],
                'url' => [$menu['route']],
            ];
            if ($data) {
                //visible
                isset($data['visible']) && $return['visible'] = $data['visible'];
                //icon
                isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
                //other attribute e.g. class...
                $return['options'] = $data;
            }
            (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'circle-o';
            $items && $return['items'] = $items;

            return $return;
        };

        // $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);

        $ret = new \stdClass();
        //
        $ret->menu = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        return $ret;
    }
}
