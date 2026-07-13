<?php
namespace api\modules\v1\controllers;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sts\V20180813\Models\GetFederationTokenRequest;

// 导入可选配置类
use TencentCloud\Sts\V20180813\StsClient;
use Yii;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="TencentCloud",
 *     description="腾讯云服务接口"
 * )
 */
class TencentCloudController extends Controller
{
    
   // public $modelClass = 'api\modules\v1\models\Meta';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [JwtHttpBearerAuth::class],
            'except' => ['options'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            // COS uploads are a normal authenticated-user capability.  Make
            // that intent explicit rather than relying on global RBAC route
            // defaults, while still rejecting anonymous requests.
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => $this->corsOrigins(),
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
    /**
     * @OA\Get(
     *     path="/v1/tencent-cloud/cloud",
     *     summary="获取云配置",
     *     description="获取腾讯云配置信息",
     *     tags={"TencentCloud"},
     *     @OA\Response(
     *         response=200,
     *         description="云配置信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="region", type="string", description="区域", example="ap-nanjing"),
     *             @OA\Property(property="bucket", type="string", description="存储桶名称")
     *         )
     *     )
     * )
     */
    public function actionCloud()
    {
        if ($this->isLocalDeployment()) {
            return [
                'driver' => 'local',
                'public' => [
                    'bucket' => getenv('LOCAL_STORAGE_PUBLIC_BUCKET') ?: 'store',
                    'baseUrl' => getenv('LOCAL_STORAGE_PUBLIC_BASE_URL') ?: '/storage',
                ],
                'private' => [
                    'bucket' => getenv('LOCAL_STORAGE_PRIVATE_BUCKET') ?: 'raw',
                ],
                'temp' => [
                    'bucket' => getenv('LOCAL_STORAGE_TEMP_BUCKET') ?: 'temp',
                ],
            ];
        }
        $cloud = Yii::$app->secret->cloud;
        return $cloud;
    }
    public function actionPublicToken()
    {
        if ($this->isLocalDeployment()) {
            return $this->featureDisabled('cos-storage');
        }
        $cloud = Yii::$app->secret->cloud['public'];
        return $this->actionToken($cloud['bucket'], $cloud['region']);
    }

    /**
     * @OA\Get(
     *     path="/v1/tencent-cloud/token",
     *     summary="获取临时密钥",
     *     description="获取腾讯云 COS 临时访问密钥（STS Token）",
     *     tags={"TencentCloud"},
     *     @OA\Parameter(
     *         name="bucket",
     *         in="query",
     *         description="存储桶名称",
     *         required=true,
     *         @OA\Schema(type="string", example="my-bucket-1234567890")
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         description="区域",
     *         required=false,
     *         @OA\Schema(type="string", example="ap-nanjing", default="ap-nanjing")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="临时密钥信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="TmpSecretId", type="string", description="临时密钥ID"),
     *             @OA\Property(property="TmpSecretKey", type="string", description="临时密钥Key"),
     *             @OA\Property(property="Token", type="string", description="临时Token"),
     *             @OA\Property(property="ExpiredTime", type="integer", description="过期时间戳")
     *         )
     *     )
     * )
     */
    public function actionToken($bucket = null, $region = 'ap-nanjing')
    {
        if ($this->isLocalDeployment()) {
            return $this->featureDisabled('cos-storage');
        }
        $cloud = Yii::$app->secret->cloud;
        // Never let a client turn this endpoint into an arbitrary-bucket STS
        // issuer.  The configured public/private buckets are the only valid
        // targets, and their configured region is authoritative.
        $allowedBuckets = array_filter([
            $cloud['bucket'] ?? null,
            $cloud['public']['bucket'] ?? null,
            $cloud['private']['bucket'] ?? null,
        ]);
        $bucket = $bucket ?: ($cloud['bucket'] ?? $cloud['public']['bucket'] ?? null);
        if (!$bucket || !in_array($bucket, $allowedBuckets, true)) {
            throw new BadRequestHttpException('Unsupported COS bucket.');
        }
        if (isset($cloud['public']['bucket'], $cloud['public']['region']) && $bucket === $cloud['public']['bucket']) {
            $region = $cloud['public']['region'];
        } elseif (isset($cloud['private']['bucket'], $cloud['private']['region']) && $bucket === $cloud['private']['bucket']) {
            $region = $cloud['private']['region'];
        }
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
        //!!$resp->StartTime = time();
        return $resp;
    }

    private function isLocalDeployment()
    {
        $mode = strtolower(getenv('DEPLOYMENT_MODE') ?: 'cloud');
        $driver = strtolower(getenv('FILE_STORAGE_DRIVER') ?: '');
        return in_array($mode, ['local', 'private'], true) || $driver === 'local';
    }

    private function featureDisabled($feature)
    {
        Yii::$app->response->statusCode = 501;
        return [
            'code' => 'FEATURE_DISABLED',
            'message' => '该功能在本地部署模式下不可用',
            'feature' => $feature,
        ];
    }

    private function corsOrigins(): array
    {
        $configured = getenv('CORS_ALLOWED_ORIGINS');
        if (!$configured) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $configured))));
    }
    
}
