<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * SchoolManagementController 处理跳转到学校管理系统的请求
 * 生成短期会话令牌并重定向到学校管理系统
 */
class SchoolManagementController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['redirect'],
                        'allow' => true,
                        'roles' => ['@'], // 仅允许已认证用户
                    ],
                ],
            ],
        ];
    }

    /**
     * 生成会话令牌并重定向到学校管理系统
     *
     * @return \yii\web\Response
     */
    public function actionRedirect()
    {
        $userId = Yii::$app->user->id;

        // 生成随机会话令牌
        $token = bin2hex(random_bytes(32));

        // 将令牌存储到 Redis，60秒过期（一次性使用）
        $redis = Yii::$app->redis;
        $key = "school_mgmt_token:{$token}";
        $data = json_encode([
            'user_id' => $userId,
            'created_at' => time(),
        ]);
        $redis->executeCommand('SET', [$key, $data, 'EX', 60]);

        // 获取学校管理系统 URL
        $schoolManagementUrl = Yii::$app->params['schoolManagementUrl'] ?? 'http://localhost:3002';

        // 重定向到学校管理系统，携带 session_token 参数
        return $this->redirect($schoolManagementUrl . '?session_token=' . $token);
    }
}
