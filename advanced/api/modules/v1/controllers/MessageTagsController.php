<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\MessageTagsSearch;
use api\modules\v1\models\MessageTags;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

use yii\web\BadRequestHttpException;
class MessageTagsController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\MessageTags';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
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
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete']);
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new MessageTagsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $dataProvider;
    }
    public  function actionDelete($id, $message_id = null, $tag_id = null){

        $model = null;
        if(isset($message_id) && isset($tag_id)){
            $model = MessageTags::findOne(['message_id'=>$message_id, 'tag_id'=>$tag_id ]);
        }else{
            $model = MessageTags::findOne($id);
        
        }
        if($model == null){
            throw new BadRequestHttpException('无效id');
        }
        if($model->message->author_id != Yii::$app->user->identity->id){
            throw new BadRequestHttpException('没有删除权限');
        }
        $model->delete();
        return $id;
    }


}
