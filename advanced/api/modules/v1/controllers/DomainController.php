<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use mdm\admin\components\AccessControl;

use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;

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
    public function actionInfo($url = null)
    {
        $request = Yii::$app->request;
        $headers = $request->headers;

        // 1. 优先尝试 Origin (通常用于跨域 API 调用)
        $origin = $headers->get('Origin');
        if ($url === null) {
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
        // 默认值
        //   return Yii::t('app', 'No Overtime Tech');
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
            Yii::$app->language = $lang;
        }

        //从url中分析出domain
        $parsedUrl = parse_url($url);

        $info = [
            'domain' => $url,
            'title' => Yii::t('app', '不加班AR创造平台'),
            'description' => Yii::t('app', '让每个人都可以快乐的创造世界'),
            'keywords' => Yii::t('app', 'AR,用户生成内容,教育,教学,不加班'),
            'author' => Yii::t('app', '上海不加班网络科技有限公司'),
            'links' => [
                [
                    'name' => Yii::t('app', '沪ICP备15039333号'),
                    'url' => 'https://beian.miit.gov.cn/',
                ],
                [
                    'name' => Yii::t('app', '上海不加班网络科技有限公司'),
                    'url' => 'https://www.bujiaban.com',
                ],
            ],
        ];

        if (!isset($parsedUrl['host'])) {

            $info['domain'] = $url;
            return $info;

        }
        $domainName = $parsedUrl['host'];// 只要二级域名
        $parts = explode('.', $domainName);

        if (count($parts) > 2) {
            $domainName = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        //增加缓存机制（缓存 key 包含语言）
        $cache = Yii::$app->cache;
        $currentLang = Yii::$app->language;

        $cacheKey = 'domain_info_' . md5($domainName . '_' . $currentLang);

        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== false) {

            return $cachedData;
        }

        $domain = $this->modelClass::findOne(['domain' => $domainName]);
        if (!$domain) {


            $info['domain'] = $domainName;
            $cache->set($cacheKey, $info, 3600); // 缓存1小时

            return $info;
        } else {
            $info['domain'] = $domain->domain;
            //多语言支持：数据库字段也用 Yii::t() 包装
            $info['title'] = $domain->title ? Yii::t('app', $domain->title) : $info['title'];
            $info['description'] = $domain->description ? Yii::t('app', $domain->description) : $info['description'];
            $info['keywords'] = $domain->keywords ? Yii::t('app', $domain->keywords) : $info['keywords'];
            $info['author'] = $domain->author ? Yii::t('app', $domain->author) : $info['author'];
            $links = [];
            foreach ($domain->links as $link) {
                $links[] = [
                    'name' => Yii::t('app', $link['name']),
                    'url' => $link['url'],
                ];
                 
            }
            $info['links'] = $links;
            $cache->set($cacheKey, $info, 3600); // 缓存1小时
            return $info;
        }



    }

}
