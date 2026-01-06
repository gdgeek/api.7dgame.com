<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use api\modules\v1\models\EduSchool;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use Yii;

class EduSchoolController extends ActiveController
{
    
    public $modelClass = 'api\modules\v1\models\EduSchool';
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
     * 获取当前用户作为校长的学校列表
     * GET /edu-school/principal
     * @return ActiveDataProvider
     */
    public function actionPrincipal()
    {
        $userId = Yii::$app->user->id;
        
        $query = EduSchool::find()
            ->where(['principal_id' => $userId]);
        
        // 搜索
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['like', 'name', $search]);
        }
        
        // 排序
        $sort = Yii::$app->request->get('sort', '-created_at');
        
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => $this->parseSortParam($sort),
                'attributes' => ['id', 'name', 'created_at', 'updated_at'],
            ],
        ]);
    }

    /**
     * 解析排序参数
     * @param string $sort 如 "-created_at" 或 "name"
     * @return array
     */
    private function parseSortParam($sort)
    {
        if (empty($sort)) {
            return ['created_at' => SORT_DESC];
        }
        
        if ($sort[0] === '-') {
            return [substr($sort, 1) => SORT_DESC];
        }
        
        return [$sort => SORT_ASC];
    }
    
}
