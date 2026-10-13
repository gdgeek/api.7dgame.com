<?php

namespace tests\unit\services;

use api\modules\v1\services\LegacyArContinuityAdapter;
use api\modules\v1\services\SessionService;
use common\components\security\ServiceUnavailableHttpException;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Request;

final class LegacyArContinuityConfigurationTest extends TestCase
{
    private array $environment;

    protected function setUp(): void
    {
        $this->environment = [];
        foreach (['AR_REFRESH_CONTINUITY_ENABLED', 'AR_REFRESH_REPLAY_KEY_BASE64', 'IDENTITY_DATA_SOURCE'] as $name) {
            $this->environment[$name] = getenv($name);
            putenv($name);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->environment as $name => $value) {
            putenv($value === false ? $name : $name . '=' . $value);
        }
    }

    public function testDefaultOffAndExplicitFalseDoNotRequireTheKey(): void
    {
        self::assertFalse(LegacyArContinuityAdapter::enabled());
        putenv('AR_REFRESH_CONTINUITY_ENABLED=false');
        self::assertFalse((new SessionService())->continuityEnabled());
    }

    public function testInvalidFeatureConfigurationFailsClosed(): void
    {
        putenv('AR_REFRESH_CONTINUITY_ENABLED=unexpected');
        $this->expectException(ServiceUnavailableHttpException::class);
        LegacyArContinuityAdapter::enabled();
    }

    public function testIndependentOwnershipCannotEnableLegacyWriters(): void
    {
        putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
        putenv('IDENTITY_DATA_SOURCE=identity');
        $this->expectException(ServiceUnavailableHttpException::class);
        LegacyArContinuityAdapter::enabled();
    }

    public function testMissingStableKeyDoesNotCreateOrDeriveOne(): void
    {
        putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
        $this->expectException(ServiceUnavailableHttpException::class);
        (new ConfigurationAdapterProbe())->engineForTest();
    }

    public function testWrongOrNoncanonicalKeyIsRejected(): void
    {
        putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
        foreach (['short', base64_encode(str_repeat('a', 31)), base64_encode(str_repeat('b', 32)) . "\n"] as $key) {
            putenv('AR_REFRESH_REPLAY_KEY_BASE64=' . $key);
            try {
                (new ConfigurationAdapterProbe())->engineForTest();
                self::fail('Invalid stable key accepted.');
            } catch (ServiceUnavailableHttpException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRedisRetrySettingMustBeTheIntegerZero(): void
    {
        $redis = Yii::$app->redis;
        $before = $redis->retries;
        putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
        putenv('AR_REFRESH_REPLAY_KEY_BASE64=' . base64_encode(str_repeat('t', 32)));
        try {
            foreach ([1, '0', 0.5, null] as $retry) {
                $redis->retries = $retry;
                try {
                    (new ConfigurationAdapterProbe())->engineForTest();
                    self::fail('Ambiguous Redis retry configuration accepted.');
                } catch (ServiceUnavailableHttpException) {
                    self::assertTrue(true);
                }
            }
            $redis->retries = 0;
            self::assertInstanceOf(\app\components\LegacyArRefreshContinuity::class, (new ConfigurationAdapterProbe())->engineForTest());
        } finally {
            $redis->retries = $before;
        }
    }

    public function testRequestContextPreservesTheOpaqueIdempotencyKey(): void
    {
        $request = new class extends Request {
            public function getUserIP() { return '127.0.0.1'; }
            public function getUserAgent() { return 'fixture-agent'; }
        };
        $request->headers->set('Idempotency-Key', 'opaque:key with spaces');
        self::assertSame(['ip' => '127.0.0.1', 'user_agent' => 'fixture-agent',
            'idempotency_key' => 'opaque:key with spaces'], (new SessionService())->contextFromRequest($request));
    }

    public function testDestructiveCompatibilityHelperCannotBypassContinuity(): void
    {
        putenv('AR_REFRESH_CONTINUITY_ENABLED=true');
        $this->expectException(ServiceUnavailableHttpException::class);
        (new SessionService())->consumeRefreshToken('fixture-credential');
    }
}

final class ConfigurationAdapterProbe extends LegacyArContinuityAdapter
{
    public function engineForTest(): \app\components\LegacyArRefreshContinuity { return $this->engine(); }
}
