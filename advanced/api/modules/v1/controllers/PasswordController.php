<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use api\modules\v1\services\PasswordResetService;
use api\modules\v1\models\RequestPasswordResetForm;
use api\modules\v1\models\ResetPasswordForm;
use api\modules\v1\models\ChangePasswordForm;
use api\modules\v1\models\VerifyResetCodeForm;

use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
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


        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options', 'request-reset', 'verify-code', 'reset'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'except' => ['options', 'request-reset', 'verify-code', 'reset'],
        ];


        /*
        // 配置响应格式为 JSON
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
        ];

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['request-reset', 'verify-code', 'verify-token', 'reset'],
        ];*/

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
            $this->passwordService->sendResetCode($form->email, $form->locale, $form->i18n);

            return [
                'success' => true,
                'message' => '找回密码验证码已发送到您的邮箱',
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
            Yii::error("Failed to send reset code: " . $e->getMessage(), __METHOD__);
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
     * 验证找回密码验证码
     * 
     * POST /v1/password/verify-code
     * 
     * @return array 响应数据
     */
    public function actionVerifyCode()
    {
        $form = new VerifyResetCodeForm();
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
            $isValid = $this->passwordService->verifyResetCode($form->email, $form->code);

            return [
                'success' => true,
                'valid' => $isValid,
                'message' => $isValid ? '验证码有效' : '验证码无效或已过期',
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
                    'message' => '验证验证码失败，请稍后重试',
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
            if (!empty($form->token)) {
                $this->passwordService->resetPassword($form->token, $form->password);
            } else {
                $this->passwordService->resetPassword($form->email, $form->code, $form->password);
            }

            return [
                'success' => true,
                'message' => '密码重置成功，请使用新密码登录',
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
     * 验证重置令牌
     *
     * POST /v1/password/verify-token
     *
     * @return array
     */
    public function actionVerifyToken()
    {
        $token = trim((string)Yii::$app->request->post('token', ''));
        if ($token === '') {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '请求参数验证失败',
                    'details' => ['token' => ['重置令牌不能为空']],
                ],
            ];
        }

        try {
            $isValid = $this->passwordService->verifyResetToken($token);
            if (!$isValid) {
                Yii::$app->response->statusCode = 400;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_TOKEN',
                        'message' => '重置令牌无效或已过期',
                    ],
                ];
            }

            return [
                'success' => true,
                'message' => '重置令牌有效',
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to verify reset token: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '校验重置令牌失败，请稍后重试',
                ],
            ];
        }
    }

    /**
     * 登录态修改密码（需邮箱已验证）
     *
     * POST /v1/password/change
     *
     * @return array
     */
    public function actionChange()
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => '用户未登录',
                ],
            ];
        }

        $form = new ChangePasswordForm();
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
            $result = $this->passwordService->changePassword(
                $user,
                $form->old_password,
                $form->new_password
            );

            if (!$result) {
                Yii::$app->response->statusCode = 500;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'CHANGE_FAILED',
                        'message' => '修改密码失败，请稍后重试',
                    ],
                ];
            }

            return [
                'success' => true,
                'message' => '密码修改成功，请重新登录',
            ];
        } catch (\yii\web\BadRequestHttpException $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to change password: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '修改密码失败，请稍后重试',
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
