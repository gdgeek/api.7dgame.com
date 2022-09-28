<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Order;
use api\modules\v1\models\Trade;

use api\modules\v1\models\TradeSearch;

use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rest\ActiveController;

class TradeController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Trade';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

// unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
// add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST'],
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

        $behaviors['authenticator'] = $auth;
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;

    }

    public function actions()
    {
        return [];
    }

    public function actionIndex()
    {   

        if(null == \Yii::$app->user->identity->wx_openid){
            return [];

        }

        $searchModel = new TradeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $dataProvider;
    }
    public function actionTransactions()
    {
        $wechat = \Yii::$app->wechat;

        $user = \Yii::$app->user->identity;

        $post = Yii::$app->request->post();
        if (!isset($post) || !isset($post['id'])) {

            throw new BadRequestHttpException('需要提供id');

        }
        $id = $post['id'];
        $trade = Trade::findOne($id);
        if (!isset($trade)) {

            throw new BadRequestHttpException('无效 id');

        }

        $order = new Order();
        $order->uuid = \Faker\Provider\Uuid::uuid();
        $order->trade_id = $trade->id;
        if ($order->validate()) {
            $order->save();
            return [
                'order_no' => $order->uuid,
                'url' => Url::to($wechat->pay_url . '?uuid=' . $order->uuid),
                'description' => $trade->description,
                'amount' => $trade->amount,
            ];
        }

    }

}
