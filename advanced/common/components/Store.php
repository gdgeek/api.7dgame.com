<?php
namespace common\components;

use yii\base\BaseObject;

/*

$tmpSecretId = "SECRETID"; //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$tmpSecretKey = "SECRETKEY"; //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$tmpToken = "COS_TOKEN"; //使用临时密钥需要填入，临时密钥生成和使用指引参见https://cloud.tencent.com/document/product/436/14048
$region = "COS_REGION"; //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket
$cosClient = new Qcloud\Cos\Client(
array(
'region' => $region,
'schema' => 'https', //协议头部，默认为http
'credentials'=> array(
'secretId'  => $tmpSecretId,
'secretKey' => $tmpSecretKey,
'token' => $tmpToken)));

 */
class Store extends BaseObject
{

    public $secretId;
    public $secretKey;
    public $bucket;
    public $region;

    public function tempKeys()
    {
        return Store::GetTempKeys($this->config());
    }
    public function officialAccount()
    {
        $config = [
            'secretId' => $this->secretId,
            'secretKey' => $this->secretKey,
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'bucket' => $this->bucket,
            'region' => $this->region,
            //...
        ];

        return Factory::officialAccount($config);
    }
    public function getClient()
    {
        $client = new \Qcloud\Cos\Client(
            array(
                'region' => $this->region,
                'schema' => 'https',
                'credentials' => array('secretId' => $this->secretId, 'secretKey' => $this->secretKey),
            )
        );
        return $client;

    }

    public function config()
    {

        return [
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => $this->secretId, //'AKIDypSO5VYrmVzEF1jiev0pMhmbFsMbHaMt',
            'secretKey' => $this->secretKey, //'QecLpjOpiK8wYbO3SbV8yc8PQCk36xYx',
            'bucket' => $this->bucket, //'mrpp-1257979353',
            'region' => $this->region, //'ap-chengdu',
            'durationSeconds' => 1800,
            'allowPrefix' => '*',
            'allowActions' => [
                'name/cos:PutObject',
                'name/cos:PostObject',
                'name/cos:HeadObject',
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload',
            ],
        ];
    }

    public static function _hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }

    // obj 转 query string
    public static function Json2str($obj, $notEncode = false)
    {
        ksort($obj);
        $arr = array();
        if (!is_array($obj)) {
            throw new Exception($obj+" must be a array");
        }
        foreach ($obj as $key => $val) {
            array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
        }
        return join('&', $arr);
    }
    // 计

    // 计算临时密钥用的签名
    public static function GetSignature($opt, $key, $method, $config)
    {
        $formatString = $method . $config['domain'] . '/?' . Store::Json2str($opt, 1);
        $sign = hash_hmac('sha1', $formatString, $key);
        $sign = base64_encode(Store::_hex2bin($sign));
        return $sign;
    }

    // v2接口的key首字母小写，v3改成大写，此处做了向下兼容
    public static function BackwardCompat($result)
    {
        if (!is_array($result)) {
            throw new Exception($result+" must be a array");
        }
        $compat = array();
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $compat[lcfirst($key)] = Store::BackwardCompat($value);
            } elseif ($key == 'Token') {
                $compat['sessionToken'] = $value;
            } else {
                $compat[lcfirst($key)] = $value;
            }
        }
        return $compat;
    }

    // 获取临时密钥
    public static function GetTempKeys($config)
    {
        if (array_key_exists('bucket', $config)) {

            $ShortBucketName = substr($config['bucket'], 0, strripos($config['bucket'], '-'));
            $AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
        }
        if (array_key_exists('policy', $config)) {
            $policy = $config['policy'];
        } else {
            $policy = array(
                'version' => '2.0',
                'statement' => array(
                    array(
                        'action' => $config['allowActions'],
                        'effect' => 'allow',
                        'principal' => array('qcs' => array('*')),
                        'resource' => array(
                            'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $config['allowPrefix'],
                        ),
                    ),
                ),
            );
        }
        $policyStr = str_replace('\\/', '/', json_encode($policy));
        $Action = 'GetFederationToken';
        $Nonce = rand(10000, 20000);
        $Timestamp = time();
        $Method = 'POST';
        $params = array(
            'SecretId' => $config['secretId'],
            'Timestamp' => $Timestamp,
            'Nonce' => $Nonce,
            'Action' => $Action,
            'DurationSeconds' => $config['durationSeconds'],
            'Version' => '2018-08-13',
            'Name' => 'cos',
            'Region' => 'ap-guangzhou',
            'Policy' => urlencode($policyStr),
        );
        $params['Signature'] = Store::GetSignature($params, $config['secretKey'], $Method, $config);
        $url = $config['url'];
        $ch = curl_init($url);
        if (array_key_exists('proxy', $config)) {
            $config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Store::Json2str($params));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }

        curl_close($ch);
        $result = json_decode($result, 1);
        if (isset($result['Response'])) {
            $result = $result['Response'];
            $result['startTime'] = $result['ExpiredTime'] - $config['durationSeconds'];
        }
        $result = Store::BackwardCompat($result);
        return $result;
    }
    // get policy
    public static function GetPolicy($scopes)
    {
        if (!is_array($scopes)) {
            return null;
        }
        $statements = array();

        for ($i = 0, $counts = count($scopes); $i < $counts; $i++) {
            $actions = array();
            $resources = array();
            array_push($actions, $scopes[$i]->get_action());
            array_push($resources, $scopes[$i]->get_resource());
            $principal = array(
                'qcs' => array('*'),
            );
            $statement = array(
                'actions' => $actions,
                'effect' => 'allow',
                'principal' => $principal,
                'resource' => $resources,
            );
            array_push($statements, $statement);
        }

        $policy = array(
            'version' => '2.0',
            'statement' => $statements,
        );
        return $policy;
    }
}
