<?php
namespace api\modules\v1\components\validator;
use yii\validators\Validator;

class JsonValidator extends Validator
{
    static public function to_string($value){
      if (!is_string($value) && !is_null($value)) {
        return json_encode($value);
      }
      return $value;
    }
    public function validateAttribute($model, $attribute)
    {
      if(is_string($model->$attribute)){
        $json = str_replace("'", '"', $model->$attribute);
        if(json_validate($json)){
            $model->$attribute = json_decode($json, true);
        }else{
            $model->addError($attribute, 'This is not JSON object!');
        }
      }
      echo is_object($model->$attribute);
      if (!is_array($model->$attribute)) {
          $model->addError($attribute, 'This is not JSON object.');
      }
    }
}