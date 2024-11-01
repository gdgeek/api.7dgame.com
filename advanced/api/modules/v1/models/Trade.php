<?php

namespace api\modules\v1\models;
use Yii;

use api\modules\v1\components\Validator\JsonValidator;
/**
 * This is the model class for table "trade".
 *
 * @property int $id
 * @property string $out_trade_no
 * @property string $description
 * @property string|null $notify_url
 * @property string $amount
 */
class Trade extends \yii\db\ActiveRecord
{

    public static function outTradeNo2Uuid($out_trade_no)
    {
        return preg_replace("/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/", "$1-$2-$3-$4-$5", $out_trade_no);
    }
    public function placeOrder($wx_openid, $time_expire, $uuid)
    {
        $out_trade_no = str_replace("-", "", $uuid);
        $wechat = \Yii::$app->wechat;
        $app = $wechat->pay();
        $api = $app->getClient();
        $account = $app->getMerchant();

        $json = [
            "mchid" => (string) $account->getMerchantId(),
            "out_trade_no" => $out_trade_no,
            "appid" => $wechat->app_id,
            "description" => $this->description,
            "notify_url" => $wechat->pay_notify_url,
            "amount" => json_decode($this->amount),
            "time_expire" => $time_expire,
            "payer" => [
                "openid" => $wx_openid,
            ],
        ];
        $response = $api->post('v3/pay/transactions/jsapi', ['body' => json_encode($json, JSON_UNESCAPED_UNICODE)]);
        $result = $response->toArray();
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trade';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['out_trade_no', 'description', 'amount'], 'required'],
            [['amount','info'], JsonValidator::class],
            [['out_trade_no', 'description', 'notify_url'], 'string', 'max' => 255],
            [['out_trade_no'], 'unique'],
        ];
    }
// 过滤掉一些字段，适用于你希望继承
    // 父类实现同时你想屏蔽掉一些敏感字段
    public function fields()
    {
        $fields = parent::fields();

        // 删除一些包含敏感信息的字段
        unset($fields['out_trade_no'], $fields['notify_url']);

        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'out_trade_no' => 'Out Trade No',
            'description' => 'Description',
            'notify_url' => 'Notify Url',
            'amount' => 'Amount',
            'info' => 'Info',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TradeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TradeQuery(get_called_class());
    }
}
