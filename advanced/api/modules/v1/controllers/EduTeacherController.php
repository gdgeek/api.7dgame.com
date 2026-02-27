<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="EduTeacher",
 *     description="教师管理接口"
 * )
 */
class EduTeacherController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\EduTeacher';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
     
        
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        
        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        
        return $behaviors;
    }
    
    /**
     * Get current user's teacher records
     * @return array
     */
    public function actionMe()
    {
        $userId = Yii::$app->user->id;

        $teachers = \api\modules\v1\models\EduTeacher::find()
            ->where(['user_id' => $userId])
            ->all();

        return $teachers;
    }
    
}
