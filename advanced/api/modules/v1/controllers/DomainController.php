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
        //从url中分析出domain
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            throw new BadRequestHttpException('无效的URL参数');
        }
        $domainName = $parsedUrl['host'];// 只要二级域名
        $parts = explode('.', $domainName);
        if (count($parts) > 2) {
            $domainName = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        //增加缓存机制
        $cache = Yii::$app->cache;
        $cacheKey = 'domain_info_' . md5($domainName);
        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== false) {
            
            return $cachedData;
        }

        $domain = $this->modelClass::findOne(['domain' => $domainName]);
        if (!$domain) {
            $info =
                [
                    'domain' => $domainName,
                    'title' => '不加班AR创作平台',
                    'info' => [
                        'description' => '不加班AR创作平台，助力教育行业快速创建AR内容，提升教学效果。',
                        'keywords' => '不加班,AR创作,教育科技,增强现实,教学工具',
                        'author' => '不加班科技',
                    ],
                ];
            $cache->set($cacheKey, $info, 3600); // 缓存1小时
            return $info;
        }



        return $domainName;

    }

}
