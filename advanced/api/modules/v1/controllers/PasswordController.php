<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use api\modules\v1\services\PasswordResetService;
use api\modules\v1\models\RequestPasswordResetForm;
use api\modules\v1\models\ResetPasswordForm;

/**
 * 密码重置控制器
 * 
 * 处理密码重置相关的 API 请求
 * 
 * @author Kiro AI
 * @since 1.0
 */
class PasswordController extends Controller
{
    /**
     * @var PasswordResetService 密码重置服务
     */
    protected $passwordService;
    
    /**
     * 初始化控制器
     */
    public function init()
    {
        parent::init();
        $this->passwordService = new PasswordResetService();
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // 配置响应格式为 JSON
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
        ];
        
        return $behaviors;
    }
    
    /**
     * 请求密码重置
     * 
     * POST /v1/password/request-reset
     * 
     * @return array 响应数据
     */
    public function actionRequestReset()
    {
        $form = new RequestPasswordResetForm();
        $form->load(Yii::$app->request->post(), '');
        
        if (!$form->validate()) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '请求参数验证失败',
                    'details' => $form->errors,
                ],
            ];
        }
        
        try {
            $this->passwordService->sendResetToken($form->email);
            
            return [
                'success' => true,
                'message' => '密码重置链接已发送到您的邮箱',
            ];
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            Yii::$app->response->statusCode = 429;
            return [
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => $e->getMessage(),
                    'retry_after' => $this->getRetryAfter($e),
                ],
            ];
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'EMAIL_NOT_VERIFIED',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to send reset token: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '发送重置链接失败，请稍后重试',
                ],
            ];
        }
    }
    
    /**
     * 验证重置令牌
     * 
     * POST /v1/password/verify-token
     * 
     * @return array 响应数据
     */
    public function actionVerifyToken()
    {
        $token = Yii::$app->request->post('token');
        
        if (empty($token)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '令牌不能为空',
                ],
            ];
        }
        
        try {
            $isValid = $this->passwordService->verifyResetToken($token);
            
            return [
                'success' => true,
                'valid' => $isValid,
                'message' => $isValid ? '令牌有效' : '令牌无效或已过期',
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to verify token: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '验证令牌失败，请稍后重试',
                ],
            ];
        }
    }
    
    /**
     * 重置密码
     * 
     * POST /v1/password/reset
     * 
     * @return array 响应数据
     */
    public function actionReset()
    {
        $form = new ResetPasswordForm();
        $form->load(Yii::$app->request->post(), '');
        
        if (!$form->validate()) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '请求参数验证失败',
                    'details' => $form->errors,
                ],
            ];
        }
        
        try {
            $this->passwordService->resetPassword($form->token, $form->password);
            
            return [
                'success' => true,
                'message' => '密码重置成功，请使用新密码登录',
            ];
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to reset password: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '密码重置失败，请稍后重试',
                ],
            ];
        }
    }
    
    /**
     * 从异常中提取 retry_after 值
     * 
     * @param \yii\web\TooManyRequestsHttpException $exception
     * @return int|null
     */
    protected function getRetryAfter(\yii\web\TooManyRequestsHttpException $exception): ?int
    {
        // 从异常消息中提取秒数
        if (preg_match('/(\d+)\s*秒/', $exception->getMessage(), $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
}
