<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Person;
use api\modules\v1\models\PersonRegister;
use api\modules\v1\models\PersonSearch;
use mdm\admin\components\AccessControl;
use mdm\admin\models\Assignment;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

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
        return [];
    }
    protected function findModel($id)
    {
        if (($model = Person::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    public function actionDelete($id)
    {
        if (Yii::$app->user->id == $id) {
            throw new Exception("不能删除自己", 400);
        }
        $model = $this->findModel($id);
        if (!$model) {
            throw new Exception(json_encode("无法找到目标", 400));

        }
        $roles = Yii::$app->user->identity->roles;
        if (in_array("root", $model->roles)) {
            throw new Exception("不能root用户", 400);
        }

        if (in_array("manager", $model->roles)) {
            if (in_array("root", $roles)) {
                throw new Exception("不能manager用户", 400);
            }
        }
        if (!in_array("root", $roles) && !in_array("manager", $roles)) {
            throw new Exception("没有删除权限", 400);
        }
        if ($model) {
            $model->delete();
            //  throw new Exception(json_encode(Yii::$app->user->identity->roles), 400);

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

    public function actionIndex()
    {
        $search = new PersonSearch();
        $dataProvider = $search->search(Yii::$app->request->queryParams);
        return $dataProvider;
    }

}
