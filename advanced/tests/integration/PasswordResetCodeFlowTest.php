<?php

namespace tests\integration;

use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\models\User;
use api\modules\v1\services\EmailService;
use api\modules\v1\services\PasswordResetService;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

final class PasswordResetCodeFlowTest extends TestCase
{
    private const EMAIL = 'password.code.flow@example.com';
    private const ORIGINAL_PASSWORD = 'OldPass123!@#';
    private const NEW_PASSWORD = 'NewPass456!@#';

    private PasswordResetService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->redis->executeCommand('PING');
        Yii::$app->db->open();

        $existing = User::findByEmail(self::EMAIL);
        if ($existing !== null) {
            $existing->delete();
        }

        $this->user = new User();
        $this->user->username = 'password_code_flow_user';
        $this->user->email = self::EMAIL;
        $this->user->setPassword(self::ORIGINAL_PASSWORD);
        $this->user->generateAuthKey();
        $this->user->email_verified_at = time();
        $this->assertTrue($this->user->save(false));

        $this->service = new PasswordResetService();
        $this->clearFlowData();
        $this->clearMailFiles();
    }

    protected function tearDown(): void
    {
        $this->clearFlowData();
        $this->clearMailFiles();
        $this->user->delete();

        parent::tearDown();
    }

    public function testCompleteResetCodeFlow(): void
    {
        $code = $this->service->sendResetCode(self::EMAIL);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $mail = $this->latestMailContent();
        $this->assertNotNull($mail);
        $this->assertStringContainsString(self::EMAIL, $mail);
        $this->assertStringContainsString($code, $mail);

        $codeKey = RedisKeyManager::getResetCodeKey(self::EMAIL);
        $stored = json_decode((string)Yii::$app->redis->executeCommand('GET', [$codeKey]), true);
        $this->assertSame($code, $stored['code'] ?? null);
        $this->assertSame((int)$this->user->id, (int)($stored['user_id'] ?? 0));
        $this->assertTrue($this->service->verifyResetCode(self::EMAIL, $code));

        $this->assertTrue(
            $this->service->resetPassword(self::EMAIL, $code, self::NEW_PASSWORD)
        );

        $this->user->refresh();
        $this->assertTrue($this->user->validatePassword(self::NEW_PASSWORD));
        $this->assertFalse($this->user->validatePassword(self::ORIGINAL_PASSWORD));
        $this->assertSame(0, (int)Yii::$app->redis->executeCommand('EXISTS', [$codeKey]));
    }

    public function testDeliveryFailureDeletesCodeAndDoesNotStartCooldown(): void
    {
        $failedDelivery = new class extends EmailService {
            public function sendPasswordResetCode(
                string $email,
                string $code,
                string $locale = 'en-US',
                array $i18n = []
            ): bool {
                return false;
            }
        };
        $this->service->setEmailDeliveryService($failedDelivery);

        try {
            $this->service->sendResetCode(self::EMAIL);
            $this->fail('Delivery failure must not be reported as success');
        } catch (ServerErrorHttpException $exception) {
            $this->assertStringContainsString('发送验证码失败', $exception->getMessage());
        }

        $codeKey = RedisKeyManager::getResetCodeKey(self::EMAIL);
        $rateLimitKey = RedisKeyManager::getRateLimitKey(self::EMAIL, 'request_reset');
        $this->assertSame(0, (int)Yii::$app->redis->executeCommand('EXISTS', [$codeKey]));
        $this->assertSame(0, (int)Yii::$app->redis->executeCommand('EXISTS', [$rateLimitKey]));
    }

    public function testUnverifiedEmailCannotRequestResetCode(): void
    {
        $this->user->email_verified_at = null;
        $this->user->save(false, ['email_verified_at']);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('邮箱未验证');
        $this->service->sendResetCode(self::EMAIL);
    }

    public function testWrongCodeCannotResetPassword(): void
    {
        $code = $this->service->sendResetCode(self::EMAIL);
        $wrongCode = $code === '000000' ? '111111' : '000000';

        try {
            $this->service->resetPassword(self::EMAIL, $wrongCode, self::NEW_PASSWORD);
            $this->fail('Wrong code must not reset the password');
        } catch (BadRequestHttpException $exception) {
            $this->assertStringContainsString('验证码不正确', $exception->getMessage());
        }

        $this->user->refresh();
        $this->assertTrue($this->user->validatePassword(self::ORIGINAL_PASSWORD));
    }

    private function clearFlowData(): void
    {
        Yii::$app->redis->executeCommand('DEL', [
            RedisKeyManager::getResetCodeKey(self::EMAIL),
            RedisKeyManager::getResetAttemptsKey(self::EMAIL),
            RedisKeyManager::getRateLimitKey(self::EMAIL, 'request_reset'),
        ]);
    }

    private function clearMailFiles(): void
    {
        $mailPath = Yii::getAlias('@runtime/mail');
        foreach (glob($mailPath . '/*.eml') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function latestMailContent(): ?string
    {
        $mailPath = Yii::getAlias('@runtime/mail');
        $files = glob($mailPath . '/*.eml') ?: [];
        if ($files === []) {
            return null;
        }

        usort($files, static function (string $left, string $right): int {
            return filemtime($right) <=> filemtime($left);
        });

        return file_get_contents($files[0]) ?: null;
    }
}
