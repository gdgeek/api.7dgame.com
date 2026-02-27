<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\models\SendVerificationForm;
use api\modules\v1\models\VerifyEmailForm;
use api\modules\v1\models\EmailCodeForm;
use api\modules\v1\models\EmailAddressForm;
use api\modules\v1\models\User;

use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;

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
            $this->emailService->sendVerificationCode($form->email, $form->locale, $form->i18n);

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
     * 验证邮箱验证码并绑定邮箱
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
            // 验证验证码并绑定邮箱
            $result = $this->emailService->verifyCode($form->email, $form->code, $form->change_token);

            if (!$result) {
                Yii::$app->response->statusCode = 500;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'BIND_FAILED',
                        'message' => '邮箱绑定失败，请稍后重试',
                    ],
                ];
            }

            // 获取更新后的用户信息
            $user = Yii::$app->user->identity;
            if (!$user instanceof User) {
                throw new \yii\web\UnauthorizedHttpException('未授权');
            }
            $user->refresh(); // 刷新用户数据

            return [
                'success' => true,
                'message' => '邮箱验证并绑定成功',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                ],
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
     * 发送测试邮件
     * 
     * GET /v1/email/test
     * 
     * @return array 响应数据
     */
    public function actionTest()
    {
        try {
            $testEmail = 'nethz@163.com';
            $fromEmail = Yii::$app->params['supportEmail'] ?? getenv('MAILER_USERNAME') ?? 'noreply@example.com';

            $result = Yii::$app->mailer->compose()
                ->setFrom([$fromEmail => Yii::$app->name . ' 测试'])
                ->setTo($testEmail)
                ->setSubject('邮件服务测试 - ' . date('Y-m-d H:i:s'))
                ->setTextBody('这是一封测试邮件，发送时间：' . date('Y-m-d H:i:s'))
                ->setHtmlBody('<h2>邮件服务测试</h2><p>这是一封测试邮件</p><p>发送时间：' . date('Y-m-d H:i:s') . '</p>')
                ->send();

            if ($result) {
                Yii::info("Test email sent successfully to {$testEmail}", __METHOD__);
                return [
                    'success' => true,
                    'message' => '测试邮件发送成功',
                    'data' => [
                        'from' => $fromEmail,
                        'to' => $testEmail,
                        'time' => date('Y-m-d H:i:s'),
                    ],
                ];
            } else {
                Yii::warning("Failed to send test email to {$testEmail}", __METHOD__);
                Yii::$app->response->statusCode = 500;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'SEND_FAILED',
                        'message' => '测试邮件发送失败',
                    ],
                ];
            }
        } catch (\Exception $e) {
            Yii::error("Error sending test email: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '发送失败：' . $e->getMessage(),
                ],
            ];
        }
    }
    private function getUser(): User
    {
        $user = Yii::$app->user->identity;
        if (!$user || !$user instanceof User) {
            throw new \yii\web\UnauthorizedHttpException('未授权');
        }
        return $user;
    }

    /**
     * 获取当前用户邮箱验证状态
     * 
     * GET /v1/email/status
     * 
     * @return array 响应数据
     */
    public function actionStatus()
    {
        $user = $this->getUser();


        $isVerified = !empty($user->email_verified_at);

        return [
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'email_verified' => $isVerified,
                'email_verified_at' => $user->email_verified_at,
                'email_verified_at_formatted' => $user->email_verified_at ? date('Y-m-d H:i:s', $user->email_verified_at) : null,
            ],
        ];
    }

    /**
     * 发送修改/解绑邮箱二次确认验证码（发到当前绑定邮箱）
     *
     * POST /v1/email/send-change-confirmation
     *
     * @return array
     */
    public function actionSendChangeConfirmation()
    {
        $user = $this->getUser();


        try {
            $locale = Yii::$app->request->post('locale', 'en-US');
            $i18n = Yii::$app->request->post('i18n', []);
            if (!is_string($locale) || !preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'locale 必须为完整格式，如 en-US',
                    ],
                ];
            }
            if (!is_array($i18n)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'i18n 必须是对象，key 为 locale',
                    ],
                ];
            }

            $this->emailService->sendCurrentEmailConfirmationCode($user, $locale, $i18n);
            return [
                'success' => true,
                'message' => '二次确认验证码已发送到当前绑定邮箱',
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
                    'code' => 'INVALID_STATE',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to send change confirmation code: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '发送二次确认验证码失败，请稍后重试',
                ],
            ];
        }
    }

    /**
     * 校验当前绑定邮箱验证码，获取改绑令牌
     *
     * POST /v1/email/verify-change-confirmation
     *
     * @return array
     */
    public function actionVerifyChangeConfirmation()
    {

        $user = $this->getUser();

        $form = new EmailCodeForm();
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
            $changeToken = $this->emailService->verifyCurrentEmailForChange($user, $form->code);
            return [
                'success' => true,
                'message' => '旧邮箱验证成功，请在 10 分钟内完成新邮箱绑定',
                'data' => [
                    'change_token' => $changeToken,
                    'expires_in' => 600,
                ],
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
            Yii::error("Failed to verify change confirmation code: " . $e->getMessage(), __METHOD__);
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
     * 解绑当前邮箱（需旧邮箱验证码）
     *
     * POST /v1/email/unbind
     *
     * @return array
     */
    public function actionUnbind()
    {

        $user = $this->getUser();


        $code = null;
        if (!empty($user->email_verified_at)) {
            $form = new EmailCodeForm();
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
            $code = $form->code;
        }

        try {
            $result = $this->emailService->unbindCurrentEmail($user, $code);
            if (!$result) {
                Yii::$app->response->statusCode = 500;
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'UNBIND_FAILED',
                        'message' => '邮箱解绑失败，请稍后重试',
                    ],
                ];
            }

            $user->refresh();
            return [
                'success' => true,
                'message' => '邮箱解绑成功',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                ],
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
                    'code' => 'INVALID_REQUEST',
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (\Exception $e) {
            Yii::error("Failed to unbind email: " . $e->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => '解绑失败，请稍后重试',
                ],
            ];
        }
    }

    /**
     * 查询验证码发送冷却时间
     *
     * GET /v1/email/cooldown?email=xxx
     *
     * @return array
     */
    public function actionCooldown()
    {

        $user = $this->getUser();
        $email = Yii::$app->request->get('email');
        if (empty($email) && $user && !empty($user->email)) {
            $email = $user->email;
        }

        $form = new EmailAddressForm();
        $form->email = (string)$email;
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

        $cooldown = $this->emailService->getSendCooldown($form->email);

        return [
            'success' => true,
            'data' => array_merge(
                ['email' => $form->email],
                $cooldown
            ),
        ];
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
