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
 *     name="EduStudent",
 *     description="学生管理接口"
 * )
 */
class EduStudentController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\EduStudent';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];
        
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
     * Get current user's student record
     * @return \api\modules\v1\models\EduStudent|null
     */
    public function actionMe()
    {
        $userId = Yii::$app->user->id;
        
        $student = \api\modules\v1\models\EduStudent::find()
            ->where(['user_id' => $userId])
            ->one();
        
        return $student;
    }

    /**
     * Join a class as student
     * @return \api\modules\v1\models\EduStudent
     * @throws BadRequestHttpException
     */
    public function actionJoin()
    {
        $classId = Yii::$app->request->post('class_id');
        
        if (empty($classId)) {
            throw new BadRequestHttpException('class_id is required');
        }

        // Check if class exists
        $class = \api\modules\v1\models\EduClass::findOne($classId);
        if (!$class) {
            throw new BadRequestHttpException('Class not found');
        }

        $userId = Yii::$app->user->id;

        // Check if already a student (user_id is unique)
        $existing = \api\modules\v1\models\EduStudent::findOne(['user_id' => $userId]);
        if ($existing) {
            throw new BadRequestHttpException('You are already in a class');
        }

        $student = new \api\modules\v1\models\EduStudent();
        $student->user_id = $userId;
        $student->class_id = $classId;

        if (!$student->save()) {
            throw new BadRequestHttpException(json_encode($student->errors));
        }

        return $student;
    }
    
}
