<?php

namespace common\components;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Text;
use yii\base\Component;
use yii\helpers\Html;
use EasyWeChat\Pay\Application;

class WeChatPay extends Component
{
    public ?int  $mch_id;
    public ?string $private_key;
    public ?string $certificate;
    public ?string $certificate_serial_no;
    public ?array $http;
    public ?string $secret_key;
    public function getApplication(){
      //  return new Application(this);
        echo json_encode($this);
    }
 /*

  'mch_id' => 1360649000,
            'private_key' => __DIR__ . '/certs/apiclient_key.pem',
            'certificate' => __DIR__ . '/certs/apiclient_cert.pem',
            'certificate_serial_no' => '6F2BADBE1738B07EE45C6A85C5F86EE343CAABC3',
            'http' => [
                'base_uri' => 'https://api.mch.weixin.qq.com/',
            ],
            // v3
            'secret_key' => '43A03299A3C3FED3D8CE7B820Fxxxxx',

    public ?string $secret;
    public ?string $app_id;
    public ?string $response_type ='array';

    
    public function officialAccount(){
        $config = [
            'app_id' => $this->app_id,
            'secret' => $this->secret,
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => $this->response_type,

            //...
        ];

        return Factory::officialAccount($config);
    }
*/

}