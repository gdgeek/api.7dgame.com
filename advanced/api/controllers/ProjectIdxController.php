<?php
namespace api\controllers;

use common\models\Project;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

use yii\helpers\Url;
class ProjectIdxController extends ActiveController
{
    public $modelClass = 'common\models\ProjectIndex';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter

        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
       
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                    'Origin' => ['*'],
                    '' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [
                        'aaaaa'
                    ],
                ],
        ];
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
     
        return $behaviors;
    }
    /*
    public static function findIdentityByAccessToken($token, $type = null)
    {
    return static::findOne(['access_token' => $token]);
    }*/
    public function actions()
    {
        $actions = parent::actions();

        // 禁用 "delete" 和 "create" 动作
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate()
    {
       
        if (isset($this->checkAccess)) {
            call_user_func($this->checkAccess, $this->id);
        }
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->createScenario,
        ]);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->author_id = Yii::$app->user->id;
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;

    }

    
    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => false,
        ]);
        return $activeData;
    }

}
