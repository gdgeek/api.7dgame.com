<?php

namespace api\modules\v1\components;

use api\modules\v1\models\AiRodin;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class AiRodinRule extends Rule
{
    public $name = 'ai_rodin_rule';
    //public $modelType = AiRodin::class;
    //public $columnName = "user_id";
    
    public function execute($user, $item, $params)
    {
        //$this->modelType = AiRodin::class;
        //  $this->columnName = 'user_id';
        // return parent::execute($user, $item, $params);
        
        $request = Yii::$app->request;
        
        $post = \Yii::$app->request->post();
        
        
        // $modelClass = $this->modelType;
        // $columnName = $this->columnName;
        if(($request->isGet || $request->isPut ||$request->isDelete) && isset($params['id'])) {
            $model = AiRodin::findOne($params['id']);
            if($model->user_id === $user){
                return true;
            }
            return false;
        }else if($request->isPost) {
            return true;
        }
        
    }
}
