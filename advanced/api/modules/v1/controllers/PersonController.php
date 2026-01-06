<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use api\modules\v1\models\PersonRegister;
use mdm\admin\components\AccessControl;
use mdm\admin\models\Assignment;
use api\modules\v1\models\PersonSearch;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class PersonController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Person';
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
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['options']);
        unset($actions['delete']);
        unset($actions['index']);
        return $actions;
    }
    public function actionIndex()
    {
        $searchModel = new PersonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $dataProvider;
    }
    public function actionDelete($id)
    {

        $user = User::findOne($id);
        if ($user == null) {
            throw new BadRequestHttpException('没有user');
        }
        $roles = Yii::$app->user->identity->roles;


        if (Yii::$app->user->identity->id == $user->id) {
            throw new BadRequestHttpException('不能删除自己');
        }
        if (in_array('root', $user->roles)) {
            throw new BadRequestHttpException('root用户不可删除');
        }

        if (in_array('root', $roles)) {
            $user->delete();
            return ['success' => true];
        } else if (in_array('admin', $roles) && !in_array('admin', $user->roles)) {
            $user->delete();
            return ['success' => true];
        } else {
            throw new BadRequestHttpException('权限不足');
        }

    }
    public function actionCreate()
    {
        $model = new PersonRegister();

        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->validate()) {
            $model->signup();
            $user = $model->getUser();
            $assignment = new Assignment($user->id);
            $assignment->assign(['user']);

            $access_token = $user->generateAccessToken();

            return [
                'access_token' => $access_token,
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

    public function actionAuth()
    {

        $post = Yii::$app->request->post();

        $roles = Yii::$app->user->identity->roles;

        if (!isset($post['id'])) {
            throw new BadRequestHttpException('缺乏 id 数据');
        }

        if (!isset($post['auth'])) {
            throw new BadRequestHttpException('缺乏 auth 数据');
        }
        $id = $post['id'];
        $auth = $post['auth'];

        $user = User::findOne($id);
        if ($user == null) {
            throw new BadRequestHttpException('没有user');
        }

        if (in_array('root', $user->roles)) {
            throw new BadRequestHttpException('root用户不可修改');
        }

        if (in_array('admin', $roles) && ($auth == 'root' || $auth == 'admin')) {
            throw new BadRequestHttpException('权限不足');
        }

        $model = new Assignment($user->id);

        switch ($auth) {
            case 'manager':

                $success = $model->revoke(['admin']);
                $success = $model->assign(['manager', 'user']);
                break;
            case 'admin':
                $success = $model->revoke(['manager']);
                $success = $model->assign(['admin', 'user']);
                break;
            case 'user':

                $success = $model->revoke(['admin', 'manager']);
                $success = $model->assign(['user']);

                break;
            default:
                throw new Exception("无效的权限", 400);
                break;
        }
        return array_merge($model->getItems(), ['success' => $success]);


    }
}
