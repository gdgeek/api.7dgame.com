<?php

namespace api\modules\v1\components\rule;
use api\modules\v1\models\Meta;


use api\modules\v1\models\AiRodin;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class UserDeriveRule extends Rule
{
  public $columnName = "user_id";
  public $modelType;
  /**
  * @var string the model class name. This property must be set.
  */
  public function execute($user, $item, $params)
  {
    
    $request = Yii::$app->request;
    
    $post = \Yii::$app->request->post();
    
    if ($this->modelType === null) {
      throw new InvalidConfigException('The "modelClass" property must be set.');
    }
    
    $modelClass = $this->modelType;
    $columnName = $this->columnName;
    if(($request->isGet || $request->isPut ||$request->isDelete) && isset($params['id'])) {
      $model = $modelClass::findOne($params['id']);
      if($model->$columnName === $user){
        return true;
      }
      return false;
    }else if($request->isPost) {
      return true;
    }
  }
}
