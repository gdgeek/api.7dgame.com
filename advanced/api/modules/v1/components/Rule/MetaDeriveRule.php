<?php

namespace api\modules\v1\components\rule;
use api\modules\v1\models\Meta;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class MetaDeriveRule extends Rule
{
   // public $type;
    public $modelType;
     /**
     * @var string the model class name. This property must be set.
     */
    public function execute($user, $item, $params)
    {

      $request = Yii::$app->request;
     
      $post = \Yii::$app->request->post();

      if ($this->modelClass === null) {
        throw new InvalidConfigException('The "modelClass" property must be set.');
      }
      $modelClass = $this->modelType;

      if($request->isGet && isset($params['meta_id'])) {
        $meta = Meta::findOne($params['meta_id']);
        if (!$meta) {
          throw new BadRequestHttpException("no meta");
        }
        if ($meta->viewable()) {
          return true;
        }
        
        return false;
      }

      if($request->isPost && isset($post['meta_id'])) {

        $meta = Meta::findOne($post['meta_id']);
        
        if (!$meta) {
          throw new BadRequestHttpException("no meta");
        }
        if ($meta->editable()) {
          return true;
        }
        return false;
      }
    

      if (($request->isGet || $request->isDelete || $request->isPut) && isset($params['id'])) {
          
          $model = ($modelClass)::findOne($params['id']);
          
          if (!$model) {
            throw new BadRequestHttpException("no model");
          }
          $meta = $model->meta;
          if (!$meta) {
            throw new BadRequestHttpException("no meta");
          }

          if ($meta->editable()) {
            return true;
          }
          if ($request->isGet && $meta->viewable()) {
            return true;
          }

          return false;
      }
    }
}
