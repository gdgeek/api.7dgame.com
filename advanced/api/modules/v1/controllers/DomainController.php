<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Domain",
 *     description="域名管理接口"
 * )
 */
class DomainController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\Domain';
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        /*
        // add CORS filter
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

        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];*/

        return $behaviors;
    }
    public function actions()
    {
        $actions = parent::actions();
        // 禁用不需要的操作
        // unset($actions['delete'], $actions['create'], $actions['update'], $actions['view'], $actions['index']);
        return [];
    }

    /**
     * @OA\Get(
     *     path="/v1/domain/info",
     *     summary="获取域名信息",
     *     description="根据请求来源获取域名的 SEO 信息和配置",
     *     tags={"Domain"},
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="域名 URL（可选，默认从请求头获取）",
     *         required=false,
     *         @OA\Schema(type="string", example="https://example.com")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         description="语言代码",
     *         required=false,
     *         @OA\Schema(type="string", example="zh-CN")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="域名信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="domain", type="string", description="域名", example="example.com"),
     *             @OA\Property(property="title", type="string", description="网站标题"),
     *             @OA\Property(property="description", type="string", description="网站描述"),
     *             @OA\Property(property="keywords", type="string", description="关键词"),
     *             @OA\Property(property="author", type="string", description="作者"),
     *             @OA\Property(property="links", type="array", description="相关链接", @OA\Items(
     *                 @OA\Property(property="name", type="string", description="链接名称"),
     *                 @OA\Property(property="url", type="string", description="链接地址")
     *             ))
     *         )
     *     )
     * )
     */
    public function actionInfo($url = null)
    {

        $request = Yii::$app->request;
        $headers = $request->headers;

        // 1. 优先尝试 Origin (通常用于跨域 API 调用)
       
        if ($url === null) {
             $origin = $headers->get('Origin');
            if ($origin) {
                $url = $origin;
            }
            // 2. 其次尝试 Referer (页面跳转或同域请求)
            elseif ($referer = $headers->get('Referer')) {
                // Referer 是完整 URL，需要解析出 scheme 和 host
                $parsed = parse_url($referer);
                if (isset($parsed['scheme']) && isset($parsed['host'])) {
                    $url = $parsed['scheme'] . '://' . $parsed['host'];
                    // 如果有端口号也加上
                    if (isset($parsed['port'])) {
                        $url .= ':' . $parsed['port'];
                    }
                } else {
                    $url = $referer;
                }
            }
            // 3. 最后回退到当前后端域名
            else {
                $url = $request->hostInfo;
            }
        }
        
        // 根据请求设置当前语言（优先 query 参数，其次 Accept-Language）
        $lang = Yii::$app->request->get('lang');

        if (!$lang) {
            $acceptLang = Yii::$app->request->headers->get('Accept-Language');
            if ($acceptLang) {
                $lang = explode(',', $acceptLang)[0];
                $lang = explode(';', $lang)[0];
            }
        }
        if ($lang) {
            // 增加校验：只允许字母、数字、短横线、下划线，防止 "Invalid language code" 异常
            if (preg_match('/^[a-z0-9_-]+$/i', $lang)) {
                Yii::$app->language = $lang;
            }
        }
       

        // 从url中分析出domain
        $parsedUrl = parse_url($url);
        
        // 提取二级域名逻辑提前，以便用于缓存 Key
        $domainName = $url;
        if (isset($parsedUrl['host'])) {
            $domainName = $parsedUrl['host'];
            $parts = explode('.', $domainName);
            if (count(value: $parts) > 2) {
                $domainName = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
            }
        }

        // 增加缓存机制（缓存 key 包含语言）
        $cache = Yii::$app->cache;
        $currentLang = Yii::$app->language;
        $cacheKey = 'domain_info_' . md5($domainName . '_' . $currentLang);

        // 1. 先查缓存，命中则直接返回，避免后续开销
        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== false) {
            return $cachedData;
        }

        // 2. 查库 (先查库，有数据则用数据，无数据后续再填默认值)
        $domain = $this->modelClass::findOne(['domain' => $domainName]);
        
        $info = [
            'domain' => $domainName,
            'title' => null,
            'description' => null,
            'keywords' => null,
            'author' => null,
            'links' => null,
        ];

        if ($domain) {
            $info['domain'] = $domain->domain;
            if ($domain->title) $info['title'] = Yii::t('app', $domain->title);
            if ($domain->description) $info['description'] = Yii::t('app', $domain->description);
            if ($domain->keywords) $info['keywords'] = Yii::t('app', $domain->keywords);
            if ($domain->author) $info['author'] = Yii::t('app', $domain->author);
            
            if (!empty($domain->links)) {
                $links = [];
                foreach ($domain->links as $link) {
                    $links[] = [
                        'name' => Yii::t('app', $link['name']),
                        'url' => $link['url'],
                    ];
                }
                $info['links'] = $links;
            }
        }
        
        // 3. 填充默认值 (仅当字段为空时才翻译默认值，避免不必要的翻译开销)
        if (empty($info['title'])) {
            $info['title'] = Yii::t('app', '不加班AR创作平台');
        }
        if (empty($info['description'])) {
            $info['description'] = Yii::t('app', '让每个人都可以快乐的创造世界。');
        }
        if (empty($info['keywords'])) {
            $info['keywords'] = Yii::t('app', 'AR, User-Generated Content, Education, Teaching, No Overtime');
        }
        if (empty($info['author'])) {
            $info['author'] = Yii::t('app', '上海不加班网络科技有限公司');
        }
        
        if (empty($info['links'])) {
            $info['links'] = [
                [
                    'name' => Yii::t('app', '沪ICP备15039333号'),
                    'url' => 'https://beian.miit.gov.cn/',
                ],
                [
                    'name' => Yii::t('app', '上海不加班网络科技有限公司'),
                    'url' => 'https://www.bujiaban.com',
                ],
            ];
        }

        // 4. 写入缓存
        $cache->set($cacheKey, $info, 3600); // 缓存1小时
       
        return $info;



    }

}
