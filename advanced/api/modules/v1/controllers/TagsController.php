<?php
namespace api\modules\v1\controllers;

use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use Yii;

use api\modules\v1\models\TagsSearch;
class TagsController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Tags';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET'],
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
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }
   
    public function actionIndex()
    {

     


        $searchModel = new TagsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);
        /*

        $tags = Yii::$app->request->get('tags');
        
        // 如果tags参数存在，将其转换为数字数组
        if ($tags) {
            $tagsArray = array_map('intval', explode(',', $tags));
            if (isset($tagsArray) && !empty($tagsArray)) {
                // 假设有一个 verse_tags 表，包含 verse_id 和 tag_id 字段
                $dataProvider->query->innerJoin('verse_tags', 'verse_tags.verse_id = verse.id')
                    ->andWhere(['in', 'verse_tags.tags_id', $tagsArray])
                    ->groupBy('verse.id'); // 避免重复结果
            }
        }


*/
       
        return $dataProvider;
    }

}
