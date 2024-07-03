<?php

namespace api\modules\v1\components\rule;
use api\modules\v1\models\Verse;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

class VerseDeriveRule extends Rule
{
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

      if($request->isGet && isset($params['verse_id'])) {
        $verse = Verse::findOne($params['verse_id']);
        if (!$verse) {
          throw new BadRequestHttpException("no verse");
        }
        if ($verse->viewable()) {
          return true;
        }
        
        return false;
      }

      if($request->isPost && isset($post['verse_id'])) {

        $verse = Verse::findOne($post['verse_id']);
        
        if (!$verse) {
          throw new BadRequestHttpException("no verse");
        }
        if ($verse->editable()) {
          return true;
        }
        return false;
      }
    

      if (($request->isGet || $request->isDelete || $request->isPut) && isset($params['id'])) {
          
          $model = ($modelClass)::findOne($params['id']);
          
          if (!$model) {
            throw new BadRequestHttpException("no model");
          }
          $verse = $model->verse;
          if (!$verse) {
            throw new BadRequestHttpException("no verse");
          }

          if ($verse->editable()) {
            return true;
          }
          if ($request->isGet && $verse->viewable()) {
            return true;
          }

          return false;
      }
    }
}
