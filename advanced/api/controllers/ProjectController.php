<?php
namespace api\controllers;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use common\models\Project;
use common\models\Programme;
use api\modules\v1\models\Resource;

class ProjectController extends ActiveController
{
    
    public $modelClass = 'common\models\Project';
    public function behaviors()
    {
        return ArrayHelper::merge([
            [
                    'class' => Cors::className(),
                    'cors' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['POST'],
                        'Access-Control-Request-Headers'=>['*']
                    ],

            ],
        ], parent::behaviors());
    }
}
