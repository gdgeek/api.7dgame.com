<?php
namespace api\controllers;
use yii\rest\ActiveController;
use Yii;
use common\models\Maker;
use common\models\Programme;
use api\modules\v1\models\Resource;

use yii\filters\auth\CompositeAuth;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class MakerController extends ActiveController
{
    public $modelClass = 'common\models\Maker';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
        return $behaviors;
    }
   
    public function actions()
    {
        $actions = parent::actions();
        unset( $actions['create']);
        return $actions;
    }
    public function actionCreate(){

        $params = Yii::$app->request->bodyParams;


        $error = new \stdClass();
        $error->name = "MrPP.com error";
        $error->code = 0;
        if(isset($params)&& isset($params['polygen_id'])&& isset($params['title']) ){
            $polygen_id = $params['polygen_id'];
            $polygen = Resource::findOne(['id'=>$polygen_id, 'type'=>'polygen']);
            if(isset($polygen)){

                $maker = new Maker();
                $programme = new Programme();
                $programme->author_id = Yii::$app->user->id;

                $maker->user_id = Yii::$app->user->id;
                $maker->polygen_id = $params['polygen_id'];
                $programme->title = $params['title'];
                if(isset($params['information'])){
                    $programme->information = $params['information'];
                }
                $programme->save();
                $maker->programme_id = $programme->id;
                $maker->save();
                return $maker;
            }else{
                 $error->message = "Polygen ID是无效的";
            }
           
        }else{
            
            $error->message = "need polygen_id and title";
        }
        
        $error->status = 400;
        $error->type = 400;

        return $error;

    }


}
