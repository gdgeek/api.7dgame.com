<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\VerseReleaseSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class VerseReleaseController extends ActiveController
{
  public $modelClass = 'api\modules\v1\models\VerseRelease';
  public function behaviors()
  {
    $behaviors = parent::behaviors();
    
    
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
  public function actionVerse(){
    
    if (!isset(Yii::$app->request->queryParams['code'])) {
      throw new BadRequestHttpException('缺乏 code 数据');
    }
    $searchModel = new VerseReleaseSearch();
    $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
    $code = HtmlPurifier::process(Yii::$app->request->queryParams['code']);
    $model = $dataProvider->query->andWhere(['code' => $code])->one();
    if($model){
      return $model->verse;
    }
    return null;
  }
  
}
