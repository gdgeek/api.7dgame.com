<?php

namespace tests\unit\contracts;

use PHPUnit\Framework\TestCase;

final class EmailDeliveryFailureContractTest extends TestCase
{
    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3) . '/' . $relativePath;
        $this->assertFileExists($path);

        return (string)file_get_contents($path);
    }

    public function testPasswordResetRejectsFailedEmailDelivery(): void
    {
        $service = $this->read('api/modules/v1/services/PasswordResetService.php');
        $controller = $this->read('api/modules/v1/controllers/PasswordController.php');

        $this->assertStringContainsString('if (!$emailSent)', $service);
        $this->assertStringContainsString("executeCommand('DEL', [\$codeKey])", $service);
        $this->assertStringContainsString("throw new ServerErrorHttpException('发送验证码失败，请稍后重试')", $service);
        $this->assertStringNotContainsString('but code was stored in Redis', $service);
        $this->assertStringContainsString("'code' => 'EMAIL_DELIVERY_FAILED'", $controller);
        $deliveryFailure = strpos($service, 'if (!$emailSent)');
        $rateLimitHit = strpos($service, '$this->rateLimiter->hit', $deliveryFailure);
        $this->assertNotFalse($deliveryFailure);
        $this->assertNotFalse($rateLimitHit);
        $this->assertLessThan($rateLimitHit, strpos($service, 'throw new ServerErrorHttpException', $deliveryFailure));
    }

    public function testPasswordResetActionsRemainPublic(): void
    {
        $controller = $this->read('api/modules/v1/controllers/PasswordController.php');

        $this->assertStringContainsString('use mdm\admin\components\AccessControl;', $controller);
        $this->assertStringContainsString(
            "'allowActions' => ['options', 'request-reset', 'verify-code', 'reset']",
            $controller
        );
    }

    public function testEmailBindingRejectsFailedDelivery(): void
    {
        $service = $this->read('api/modules/v1/services/EmailVerificationService.php');
        $controller = $this->read('api/modules/v1/controllers/EmailController.php');

        $this->assertStringContainsString('if (!$emailSent)', $service);
        $this->assertStringContainsString("executeCommand('DEL', [\$codeKey])", $service);
        $this->assertStringNotContainsString('but code was stored in Redis', $service);
        $this->assertStringContainsString(
            "throw new ServerErrorHttpException('发送验证码失败，请稍后重试')",
            $service
        );
        $this->assertStringContainsString("'code' => 'EMAIL_DELIVERY_FAILED'", $controller);
        $this->assertStringContainsString(
            "throw new ServerErrorHttpException('邮箱绑定失败，请稍后重试')",
            $service
        );
        $this->assertStringContainsString("'code' => 'BIND_FAILED'", $controller);
    }

    public function testVerifiedEmailUnbindUsesTheSubmittedCode(): void
    {
        $service = $this->read('api/modules/v1/services/EmailVerificationService.php');

        $this->assertStringContainsString(
            'public function unbindCurrentEmail(User $user, ?string $code): bool',
            $service
        );
        $this->assertStringContainsString(
            '$this->assertCodeValid($user->email, trim($code));',
            $service
        );
        $this->assertStringNotContainsString(
            '$this->assertEmailChangeTokenValid((int)$user->id, $changeToken);',
            $service
        );
    }

    public function testOnlyLegacyEmailAddressesAreBackfilledAsVerified(): void
    {
        $migration = $this->read(
            'console/migrations/m260716_000000_backfill_legacy_email_verification.php'
        );

        $this->assertStringContainsString(
            'private const VERIFICATION_FEATURE_RELEASED_AT = 1768964608;',
            $migration
        );
        $this->assertStringContainsString("['email_verified_at' => null]", $migration);
        $this->assertStringContainsString("['not', ['email' => null]]", $migration);
        $this->assertStringContainsString("['<>', 'email', '']", $migration);
        $this->assertStringContainsString(
            "['<', 'created_at', self::VERIFICATION_FEATURE_RELEASED_AT]",
            $migration
        );
    }
}
