<?php
namespace common\components;
use api\modules\v1\models\Resource;
use yii\rbac\Rule;
use Yii;
class ResourceRule extends Rule
{
    public $name = 'resource_rule';
    public function execute($user, $item, $params)
    {
        
        
        $request = Yii::$app->request;
        $post = \Yii::$app->request->post();
        
        if($request->isGet && !isset($params['id'])) {
            return true;
        }
        if(($request->isGet || $request->isPut ||$request->isDelete) && isset($params['id'])) {
            $model = Resource::findOne($params['id']);
            if($model && $model->author_id === $user){
                return true;
            }
            return false;
        }else if($request->isPost) {
            return true;
        }
        return false;
        
        
    }
}
