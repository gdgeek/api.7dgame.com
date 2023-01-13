<?php
namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;


use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


use mdm\admin\components\MenuHelper; 
class MenuController extends ActiveController
{
    public $modelClass = 'common\models\Menu';
    
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
   /* public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }*/
    public function actions()
    {
        $actions = parent::actions();

        // 禁用 "delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['post'], $actions['get'], $actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $callback = function($menu){ 
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


        $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        return $items;
    }

    
   
}


/*
class MenuController extends ActiveController
{
    public $modelClass = 'common\models\Menu';


    public function behaviors()
    {
        $behaviors = parent::behaviors();
    
        // remove authentication filter
      
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            HttpBasicAuth::className(),
            HttpBearerAuth::className(),
            QueryParamAuth::className(),
            ],
        ];
        $auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
    
        return $behaviors;
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
  
  
    public function actions() {
        $actions = parent::actions();
       // unset($actions['index']);
        return [];
    }
    
    public function actionIndex() {
        $callback = function($menu){ 
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


        $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        return $items;
    }

}
*/