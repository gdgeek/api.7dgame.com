<?php

namespace tests\unit\services;

use api\modules\v1\services\LoginCodeSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

final class LoginCodeSettingsTest extends TestCase
{
    public function testDefaultsPreserveDatabaseOnlyBehavior(): void
    {
        $settings = new LoginCodeSettings();

        $this->assertSame(LoginCodeSettings::READ_DATABASE, $settings->readMode());
        $this->assertSame(LoginCodeSettings::WRITE_DATABASE, $settings->writeMode());
        $this->assertFalse($settings->usesRedis());
        $this->assertTrue($settings->legacyDbAvailable());
        $this->assertSame(5, $settings->issueLimit());
        $this->assertSame(LoginCodeSettings::defaultProtocolFingerprint(), $settings->protocolFingerprint());
    }

    #[DataProvider('supportedModes')]
    public function testAcceptsOnlySupportedReadWritePairs(string $readMode, string $writeMode): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => $readMode,
            'writeMode' => $writeMode,
        ]);

        $this->assertSame($readMode, $settings->readMode());
        $this->assertSame($writeMode, $settings->writeMode());
    }

    /** @return iterable<string, array{0: string, 1: string}> */
    public static function supportedModes(): iterable
    {
        yield 'legacy baseline' => ['database', 'database'];
        yield 'shadow writes' => ['database', 'dual'];
        yield 'consumer rollout' => ['redis-first', 'dual'];
        yield 'fallback wind-down' => ['redis-first', 'redis'];
        yield 'redis only final state' => ['redis', 'redis'];
    }

    public function testRejectsUnsupportedModePair(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'dual',
        ]);
    }

    public function testLegacyTableAbsencePermitsOnlyRedisOnlyMode(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'legacyDbAvailable' => false,
        ]);

        $this->assertFalse($settings->legacyDbAvailable());
    }

    public function testRejectsLegacyTableAbsenceWithFallbackMode(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'redis',
            'legacyDbAvailable' => false,
        ]);
    }

    public function testRejectsProtocolTimeWindowDrift(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings(['activeWindowSeconds' => 61]);
    }

    public function testRejectsUnlimitedOrOutOfRangeIssueLimits(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings(['issueLimit' => 21]);
    }

    public function testRejectsNonIntegralNumericConfigurationInsteadOfCoercingIt(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings(['issueLimit' => '5.9']);
    }

    public function testRejectsUnrecognizedLegacyDatabaseFlag(): void
    {
        $this->expectException(InvalidConfigException::class);

        new LoginCodeSettings(['legacyDbAvailable' => 'sometimes']);
    }

    public function testRedisModeRejectsADeploymentFingerprintForAnotherPrefix(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('LOGIN_CODE_PROTOCOL_FINGERPRINT does not match');

        new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:login-code:v1',
            'protocolFingerprint' => LoginCodeSettings::defaultProtocolFingerprint(),
        ]);
    }

    public function testRedisModeRejectsAnExplicitlyEmptyDeploymentFingerprint(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('LOGIN_CODE_PROTOCOL_FINGERPRINT must not be empty');

        new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'protocolFingerprint' => '',
        ]);
    }

    public function testDatabaseOnlyModeAllowsAnExplicitlyEmptyDeploymentFingerprint(): void
    {
        $settings = new LoginCodeSettings([
            'protocolFingerprint' => '',
        ]);

        $this->assertFalse($settings->usesRedis());
        $this->assertSame(LoginCodeSettings::defaultProtocolFingerprint(), $settings->protocolFingerprint());
    }

    public function testRedisModeAllowsAnOmittedDeploymentFingerprintForDirectConstructorTests(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
        ]);

        $this->assertSame(LoginCodeSettings::defaultProtocolFingerprint(), $settings->protocolFingerprint());
    }

    public function testRedisModeAcceptsTheExactSharedProtocolFingerprint(): void
    {
        $fingerprint = LoginCodeSettings::protocolFingerprintFor('test:login-code:v1');
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:login-code:v1',
            'protocolFingerprint' => $fingerprint,
        ]);

        $this->assertSame($fingerprint, $settings->protocolFingerprint());
    }
}
