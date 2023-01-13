<?php
namespace api\controllers;

use stdClass;
use Yii;
use yii\rest\ActiveController;


use yii\helpers\ArrayHelper;
use yii\filters\Cors;


class LogicController extends ActiveController
{
    public $modelClass = 'common\models\Logic';

    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                    'class' => Cors::class,
                    'cors' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['POST','PUT'],
                        'Access-Control-Request-Headers'=>['*']
                    ],
            ],
        ], parent::behaviors());
    }
   
      /**
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTest()
    {
      
        return new stdClass();
    }

}
