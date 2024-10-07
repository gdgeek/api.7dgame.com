<?php

namespace api\modules\v1\components;

use api\modules\v1\models\AiRodin;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class AiRodinRule extends Rule
{
    public $name = 'ai_rodin_rule';
    public $columnName = "user_id";
    public $modelType = AiRodin::class;
    public function execute($user, $item, $params)
    {
        
        
        $request = Yii::$app->request;
        $post = \Yii::$app->request->post();
        
        if(($request->isGet || $request->isPut ||$request->isDelete) && isset($params['id'])) {
            $model = AiRodin::findOne($params['id']);
            if($model && $model->user_id === $user){
                return true;
            }
            return false;
        }else if($request->isPost) {
            return true;
        }
        
    }
}
