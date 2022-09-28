<?php

namespace common\components;

use linslin\yii2\curl;
use yii\base\Component;

class WeChat extends Component
{

    public $app_id;
    public $secret;
    public $token;
    public $mch_id;
    public $private_key;
    public $certificate;
    public $secret_key;
    public $platform_certs;
    public $pay_notify_url;
    public $pay_url;
    private function getPath()
    {
        $runtime = \Yii::getAlias('@common') . '/runtime';
        if (!file_exists($runtime)) {
            mkdir($runtime, 0777);
            chmod($runtime, 0777);
        }

        $certs = $runtime . '/certs';
        if (!file_exists($certs)) {
            mkdir($certs, 0777);
            chmod($certs, 0777);

        }
        return $certs . '/';
    }
    public function clearCertificates($datas)
    {
        $list = [];

        $path = $this->getPath();

        foreach ($datas['data'] as $item) {
            $list[$path . 'wechatpay_' . $item['serial_no'] . '.pem'] = true;
        }
        $certificates = $this->getCertificates();
        foreach ($certificates as $cert) {
            if (!array_key_exists($cert, $list)) {
                unlink($cert);
            }
        }
    }
    public function updateCertificates($datas)
    {
        $path = $this->getPath();
        $this->clearCertificates($datas);
        $aes = new AesUtil($this->secret_key);
        foreach ($datas['data'] as $item) {
            $file = $path . 'wechatpay_' . $item['serial_no'] . '.pem';
            if (!file_exists($file)) {

                $content = $aes->decryptToString($item['encrypt_certificate']['associated_data'], $item['encrypt_certificate']['nonce'], $item['encrypt_certificate']['ciphertext']);

                $handle = fopen($file, 'w');
                fwrite($handle, $content);
                fclose($handle);
                chmod($file, 0777);

            }
        }

    }
    private function getCertificates()
    {

        $path = $this->getPath();
        $files = scandir($path);
        $certlist = [];
        foreach ($files as $file) {
            if (preg_match('/^wechatpay_[^.]+.pem$/', $file, $matches)) {
                //   echo $certs . '/' . $file;
                $certlist[] = $path . $file;
            }
        }
        return $certlist;

    }
    public function certificates()
    {

        $curl = new curl\Curl();
        $app = $this->pay();
        $api = $app->getClient();

        $response = $api->get('https://api.mch.weixin.qq.com/v3/certificates');

        return $response->toArray();

    }
    private function getConfig()
    {
        $config = [];
        foreach ($this as $key => $val) {

            $config[$key] = $val;
        }
        $certificates = $this->getCertificates();
        if (count($certificates) > 0) {
            $config['platform_certs'] = $certificates;
        }
        return $config;
    }
    public function pay()
    {

        $app = new \EasyWeChat\Pay\Application($this->getConfig());
        return $app;

    }
    public function application()
    {

        $app = new \EasyWeChat\OfficialAccount\Application($this->getConfig());
        return $app;
        // return Factory::officialAccount($config);
    }
    public function signature($jsapi_ticket, $timestamp, $url)
    {
       // $timestamp = 1648512360;
        $noncestr = \Yii::$app->getSecurity()->generateRandomString(16);
        $option = [
            'noncestr' => $noncestr,
            'jsapi_ticket' => $jsapi_ticket,
            'timestamp' => (string) $timestamp,
            'url' => $url,
        ];
        ksort($option);
        $string1 = null;
        foreach ($option as $key => $value) {
            if ($string1 == null) {
                $string1 .= $key . '=' . $value;
            } else {
                $string1 .= '&' . $key . '=' . $value;
            }
        }
        $signature = sha1($string1); //$optionString;
        
        $parameter = [
            'string1'=> $string1,
            'appid' => $this->app_id,
            'signature' => $signature,
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'jsapi_ticket' => $jsapi_ticket,
            'url' => $url,
        ];
        return $parameter;
    }

}


/*
{"string1":"jsapi_ticket=LIKLckvwlJT9cWIhEQTwfKFcpFdSS7xzXnMcYxBib_nKrAQ1duAAqXEjGpgX6RQbQev-r8HW39aWUNsO7tPZng&noncestr=BMkRsjUa0eJ5pgZA×tamp=1648517406&url=https%3A%2F%2Fadmin.t.mrpp.com%2Fwechat%2Fpay%3Fuuid%3Dcbf1e8ba-754e-30e4-884f-734780b32c8f",
    "appid":"wx6f81800f15c9a88c",
    "signature":"dbfacbe726dd83fbef0c7edba116f2260b05e634",
    "timestamp":"1648517406",
    "nonceStr":"BMkRsjUa0eJ5pgZA",
    "jsapi_ticket":"LIKLckvwlJT9cWIhEQTwfKFcpFdSS7xzXnMcYxBib_nKrAQ1duAAqXEjGpgX6RQbQev-r8HW39aWUNsO7tPZng",
    "url":"https%3A%2F%2Fadmin.t.mrpp.com%2Fwechat%2Fpay%3Fuuid%3Dcbf1e8ba-754e-30e4-884f-734780b32c8f"}
    */