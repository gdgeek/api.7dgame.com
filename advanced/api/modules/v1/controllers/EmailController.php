<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\models\SendVerificationForm;
use api\modules\v1\models\VerifyEmailForm;

/**
 * 邮箱验证控制器
 * 
 * 处理邮箱验证相关的 API 请求
 * 
 * @author Kiro AI
 * @since 1.0
 */
class EmailController extends Controller
{
    /**
     * @var EmailVerificationService 邮箱验证服务
     */
    protected $emailService;
    
    /**
     * 初始化控制器
     */
    public function init()
    {
        parent::init();
        $this->emailService = new EmailVerificationService();
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
     * 发送邮箱验证码
     * 
     * POST /v1/email/send-verification
     * 
     * @return array 响应数据
     */
    public function actionSendVerification()
    {
        $form = new SendVerificationForm();
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
            $this->emailService->sendVerificationCode($form->email);
            
            return [
                'success' => true,
                'message' => '验证码已发送到您的邮箱',
            ];
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            Yii::$app->response->statusCode = 429;
            return [
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => $e->getMessage(),
                    'retry_after' => $e->statusCode === 429 ? $this->getRetryAfter($e) : null,
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to send verification code: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '发送验证码失败，请稍后重试',
                ],
            ];
        }
    }
    
    /**
     * 验证邮箱验证码
     * 
     * POST /v1/email/verify
     * 
     * @return array 响应数据
     */
    public function actionVerify()
    {
        $form = new VerifyEmailForm();
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
            $this->emailService->verifyCode($form->email, $form->code);
            
            return [
                'success' => true,
                'message' => '邮箱验证成功',
            ];
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            Yii::$app->response->statusCode = 429;
            return [
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_LOCKED',
                    'message' => $e->getMessage(),
                    'retry_after' => $this->getRetryAfter($e),
                ],
            ];
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CODE',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to verify code: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '验证失败，请稍后重试',
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
