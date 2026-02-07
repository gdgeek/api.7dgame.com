<?php

namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use common\components\HealthService;

/**
 * HealthController 处理健康检查请求
 */
class HealthController extends Controller
{
    private HealthService $_healthService;

    public function init(): void
    {
        parent::init();
        
        if (Yii::$app->has('healthService')) {
            $this->_healthService = Yii::$app->healthService;
        } else {
            $this->_healthService = new HealthService();
        }
    }

    /**
     * 禁用认证，允许公开访问
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * GET /health
     */
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Type', 'application/json');
        
        $healthStatus = $this->_healthService->check();
        
        if ($healthStatus['status'] === 'unhealthy') {
            Yii::$app->response->statusCode = 503;
        } else {
            Yii::$app->response->statusCode = 200;
        }
        
        return $healthStatus;
    }
}
