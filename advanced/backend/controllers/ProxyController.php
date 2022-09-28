<?php

namespace backend\controllers;

use api\modules\v1\models\File;
use Yii;
use yii\helpers\Url;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
/**
 * ProxyController implements the CRUD actions for Polygen model.
 */
class ProxyController extends Controller
{

    public $enableCsrfValidation = true;
  
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        

        $behaviors = parent::behaviors();
    
        // remove authentication filter
      
        // unset($behaviors['authenticator']);
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
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
    
        return $behaviors;
    }

    /**
     * Lists all Polygen models.
     * @return mixed
     */
    public function actionIndex()
    {   
        $request = Yii::$app->request;
        $target = null;
        if ($request->isGet){
            $target = $request->get('target');
        }else if($request->isPost){
            $target = $request->get('target');
        }

        if($target != null){

            return $this->redirect($target);
        }
       
        return ‘’;
    }

    
}
