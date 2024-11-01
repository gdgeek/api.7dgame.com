<?php
namespace api\modules\v1\controllers;
use Yii;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use api\modules\v1\models\AiRodinSearch;
use api\modules\v1\models\AiRodin;
use api\modules\v1\models\File;
use api\modules\v1\models\Resource;
use yii\base\Exception;

class AiRodinController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\AiRodin';
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
        
        // unset($behaviors['authenticator']);
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
        unset($actions['index']);
        return $actions;
    }
    public function actionFile($id){
        
        $aiRodin = AiRodin::findOne($id);
        if(!$aiRodin){
            throw new \yii\web\NotFoundHttpException("AiRodin not found");
        }
        $file = new File();
        $file->load(Yii::$app->request->post(),'');
        if($file->validate()){
            $file->save();
            $resource = new Resource();
            $resource->file_id = $file->id;
            $resource->type = 'polygen';
            $resource->name = $file->filename;
            if($resource->validate()){
                $resource->save();
            }else{
                $file->delete();
                throw new Exception(json_encode($resource->errors), 400);
            }
            $aiRodin->resource_id = $resource->id;
            if($aiRodin->validate()){
                $aiRodin->save();
                return $aiRodin;
            }else{
                $resource->delete();
                throw new Exception("AiRodin not found");
            }
        }else{
            throw new Exception(json_encode($file->errors), 400);
        }
    }
    public function actionIndex()
    {
        $searchModel = new AiRodinSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['user_id' => Yii::$app->user->id]);
        return $dataProvider;
    }
}
