<?php
namespace backend\controllers;

use api\modules\v1\models\Order;
use api\modules\v1\models\Token;
use api\modules\v1\models\Trade;
use api\modules\v1\models\User;
use EasyWeChat\OfficialAccount\Application;
use yii\web\Controller;

/**
 * MethodController implements the CRUD actions for Method model.
 */
class WechatController extends Controller
{

    public $layout = '@backend/views/layouts/wechat/main.php';
    public $enableCsrfValidation = false;
    public function actionMenu()
    {
        $wechat = \Yii::$app->wechat;
        $app = $wechat->application();
        $api = $app->getClient();

        $json =
            [
            "button" =>
            [
                [
                    "name" => "MrPP.com",
                    "sub_button" =>
                    [
                        [
                            "type" => "view",
                            "name" => "获得邀请码",
                            "url" => "https://mrpp.com/info/info/",
                        ],
                        [
                            "type" => "view",
                            "name" => "访问平台",
                            "url" => "https://mrpp.com",
                        ],
                    ],
                ],
            ],
        ];
        $response = $api->post('/cgi-bin/menu/create', ['body' => json_encode($json, JSON_UNESCAPED_UNICODE)]);

        var_dump($response->toArray());

    }
    public function actionTest()
    {

        \Yii::info('json_encode($message)', "mrpp");

    }
    private function checkSignature()
    {

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = "hololens2";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    private function getUserByWXOpenid($openid)
    {
        $user = User::findOne(['wx_openid' => $openid]);
        return $user;
    }
    public function actionPay($uuid)
    {
        $wechat = \Yii::$app->wechat;

        $timestamp = time();

        $token = Token::jsapiToken();
        $jsapi_ticket = $token->token;

        $url = \Yii::$app->urlManager->createAbsoluteUrl(\Yii::$app->request->url, 'https');

        $parameter = $wechat->signature($jsapi_ticket, (string) $timestamp, $url);

        $overtime = $timestamp + 600;

        $time_expire = date('Y-m-d\TH:i:s+08:00', $overtime);

        $order = Order::findOne(['uuid' => $uuid]);
        $trade = $order->trade;
        try {
            $result = $trade->placeOrder($order->user->wx_openid, $time_expire, $uuid);
        } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {

            return $this->render('error', ['exception' => $e, 'parameter' => $parameter]);

        }
        $order->prepay_id = $result['prepay_id'];
        $order->state = 1;
        $order->save();

        $app = $wechat->pay();
        $utils = $app->getUtils();

        $config = $utils->buildSdkConfig($order->prepay_id, $wechat->app_id);
        return $this->render('pay', ['order' => $order, 'parameter' => $parameter, 'config' => $config, 'overtime' => $overtime]);

    }
    public function actionQrcode()
    {
        $wechat = \Yii::$app->wechat;
        $app = $wechat->application();

        $api = $app->getClient();
        $json =
            [
            "expire_seconds" => 6 * 24 * 3600,
            "action_name" => "QR_STR_SCENE",
            "action_info" =>
            [
                "scene" => ["scene_str" => "test"],
            ],
        ];

        $response = $api->post('/cgi-bin/qrcode/create', ['body' => json_encode($json, JSON_UNESCAPED_UNICODE)]);

        $result = $response->toArray();

        return json_encode($result);
    }
    private function saveTokenAndOpenID($token, $openid, $user)
    {
        $model = new \common\models\Wx();
        $model->setup($token, $openid);
        if ($user != null) {
            $model->setUserId($user->id);
        }
        if ($model->validate()) {
            $model->save();
        }
    }

    private function qrcodeEvent($message)
    {
        if ($message['Event'] == 'SCAN' || $message['Event'] == 'subscribe') {

            $prefix = 'qrscene_';
            $token = $message['EventKey'];
            $token = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $token);
            $openid = $message['FromUserName'];
            $user = $this->getUserByWXOpenid($openid);
            $this->saveTokenAndOpenID($token, $openid, $user);
            return '欢迎登入【混合现实编程平台】';

        }
        return '扫描二维码';
    }
    private function qrcodeText($message)
    {
        return '感谢您的关注';
    }
    private function qrcodeVideo($message)
    {
        return '给我看啥？有趣吗？';
    }
    public function actionPayNotify()
    {

        $this->layout = false;

        $wechat = \Yii::$app->wechat;

        $app = $wechat->pay();

        $server = $app->getServer();

        $server->handlePaid(function (\EasyWeChat\Pay\Message $message, \Closure $next) use ( $app ) {

            \Yii::info(123, "mrpp");

           // $uuid = Trade::outTradeNo2Uuid($message->out_trade_no);

//\Yii::info($uuid, "mrpp");

            $transaction_id = $message->transaction_id;

            $api = $app->getClient();


            $account = $app->getMerchant();


            $url = 'v3/pay/transactions/id/'.$transaction_id.'?mchid='.$account->getMerchantId();
            $response = $api->get($url);



            $data = $response->toArray();
            \Yii::info(json_encode($response->toArray()), "mrpp");

            $openid = $data['payer']['openid'];
            \Yii::info($openid, "mrpp");

            $out_trade_no = $data['out_trade_no'];
\Yii::info($out_trade_no, "mrpp");
           $time = strtotime($data['success_time']);
 
\Yii::info($time, "mrpp");

            $trade_state = $data['trade_state'];
\Yii::info($trade_state, "mrpp");

            if($trade_state == 'SUCCESS'){
                $uuid = Trade::outTradeNo2Uuid($out_trade_no);

\Yii::info($uuid, "mrpp");

                $order = Order::findOne(['uuid' => $uuid]);
                if($order->user->wx_openid == $openid){
                    $order->state = 2;
                    $order->payed_time = date('Y-m-d H:i:s', $time);
                    $order->save();
                }
            }



            // $message->out_trade_no 获取商户订单号
            // $message->payer['openid'] 获取支付者 openid
            // 注意：推送信息不一定靠谱哈，请务必验证
            // 建议是拿订单号调用微信支付查询接口，以查询到的订单状态为准
            //$message->out_trade_no
            return $next($message);
        });

        $response = $server->serve();
        $this->response->setStatusCode(200);

        return $this->render('notify', ['result' => ['code' => 'SUCCESS', 'message' => '']]);

    }
    public function actionIndex()
    {
        $this->layout = false;

        $wechat = \Yii::$app->wechat;
        $app = $wechat->application();

        $server = $app->getServer();
        $server->with(function ($message, \Closure $next) {

            if (isset($message['MsgType'])) {
                switch ($message['MsgType']) {
                    case 'event':
                        return $this->qrcodeEvent($message);
                        break;
                    case 'text':
                        return $this->qrcodeText($message);
                        break;
                    case 'video':
                        return $this->qrcodeVideo($message);
                        break;
                }

            }

            return '感谢您的关注！';
        });

        $response = $server->serve();
        return $this->render('renderer', [
            'response' => $response,
        ]);

    }
}
