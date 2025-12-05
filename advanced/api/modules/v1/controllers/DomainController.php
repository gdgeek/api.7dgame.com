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
    public function actionInfo($url)
    {
        return Yii::t('app', 'No Overtime Tech');
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
            'title' => Yii::t('app', 'AR UGC Platform'),

            'description' => Yii::t('app', 'AR UGC Platform helps the education industry quickly create AR content and improve teaching effectiveness.'),
            'keywords' => Yii::t('app', 'AR UGC,AR Creation,EdTech,Augmented Reality,Teaching Tools'),
            'author' => Yii::t('app', 'AR UGC Tech'),

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
            if (is_array($domain->info)) {
                $info['description'] = isset($domain->info['description']) 
                    ? Yii::t('app', $domain->info['description']) 
                    : $info['description'];
                $info['keywords'] = isset($domain->info['keywords']) 
                    ? Yii::t('app', $domain->info['keywords']) 
                    : $info['keywords'];
                $info['author'] = isset($domain->info['author']) 
                    ? Yii::t('app', $domain->info['author']) 
                    : $info['author'];
            }
            $info['info'] = $domain->info;
            $cache->set($cacheKey, $info, 3600); // 缓存1小时
            return $info;
        }



    }

}
