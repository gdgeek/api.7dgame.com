<?php
namespace api\modules\v1\controllers;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sts\V20180813\Models\GetFederationTokenRequest;

// 导入可选配置类
use TencentCloud\Sts\V20180813\StsClient;
use Yii;
use yii\rest\ActiveController;

class TencentCloudController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Meta';
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

        return $behaviors;
    }
    public function actions()
    {
        return [];
    }
    public function actionStore()
    {
        $secret = \Yii::$app->secret;
        return $secret->store;
    }
    public function actionCloud()
    {
        $cloud = \Yii::$app->secret->cloud;
        return $cloud;
    }
    public function actionToken($bucket, $region = 'ap-nanjing')
    {

        $cred = new Credential(Yii::$app->secret->id, Yii::$app->secret->key);

        // 实例化一个http选项，可选的，没有特殊需求可以跳过
        $httpProfile = new HttpProfile();
        // 配置代理
        // $httpProfile->setProxy("https://ip:port");
        $httpProfile->setReqMethod("POST"); // post请求(默认为post请求)
        $httpProfile->setReqTimeout(30); // 请求超时时间，单位为秒(默认60秒)
        $httpProfile->setEndpoint("sts.tencentcloudapi.com"); // 指定接入地域域名(默认就近接入)

        // 实例化一个client选项，可选的，没有特殊需求可以跳过
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("TC3-HMAC-SHA256"); // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);

        $client = new StsClient($cred, $region, $clientProfile);

        // 实例化一个请求对象
        $req = new GetFederationTokenRequest();
        $req->Name = "mrpp";
        $ShortBucketName = substr($bucket, 0, strripos($bucket, '-'));
        $AppId = substr($bucket, 1 + strripos($bucket, '-'));
        $policy = array(
            'version' => '2.0',
            'statement' => array(
                array(
                    'action' => [
                        'name/cos:PutObject',
                        'name/cos:PostObject',
                        'name/cos:HeadObject',
                        'name/cos:GetObject',
                        'name/cos:InitiateMultipartUpload',
                        'name/cos:ListMultipartUploads',
                        'name/cos:ListParts',
                        'name/cos:UploadPart',
                        'name/cos:CompleteMultipartUpload',
                    ],
                    'effect' => 'allow',
                    'principal' => array('qcs' => array('*')),
                    'resource' => array(
                        'qcs::cos:' . $region . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . '*',
                    ),
                ),
            ),
        );
        $policyStr = str_replace('\\/', '/', json_encode($policy));
        $req->Policy = urlencode($policyStr);

        // 通过client对象调用想要访问的接口，需要传入请求对象
        $resp = $client->GetFederationToken($req);
        $resp->StartTime = time();
        return $resp;
    }

}
