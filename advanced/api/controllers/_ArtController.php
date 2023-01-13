<?php
namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;


class ArtController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\User';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
        return $behaviors;
    }


}