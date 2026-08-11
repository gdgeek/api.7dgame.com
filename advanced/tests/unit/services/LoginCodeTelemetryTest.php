<?php

namespace tests\unit\services;

use api\modules\v1\services\LoginCodeTelemetry;
use PHPUnit\Framework\TestCase;

final class LoginCodeTelemetryTest extends TestCase
{
    public function testItEmitsOnlyFixedLowCardinalityFields(): void
    {
        $logger = \Yii::getLogger();
        $before = count($logger->messages);

        LoginCodeTelemetry::record('issued', 'main-api-issue');

        $message = $logger->messages[$before] ?? null;
        $this->assertIsArray($message);
        $this->assertSame([
            'event' => 'issued',
            'source' => 'main-api-issue',
        ], $message[0]);
        $this->assertSame(['event', 'source'], array_keys($message[0]));
        $this->assertSame('login-code', $message[2]);
        $this->assertArrayNotHasKey('user', $message[0]);
        $this->assertArrayNotHasKey('user_id', $message[0]);
        $this->assertArrayNotHasKey('code', $message[0]);
        $this->assertArrayNotHasKey('digest', $message[0]);
        $this->assertArrayNotHasKey('token', $message[0]);
    }

    public function testItRejectsUnknownDimensionsWithoutLoggingCallerInput(): void
    {
        $logger = \Yii::getLogger();
        $before = count($logger->messages);
        $rawCode = 'code-secret-' . str_repeat('a', 48);
        $digest = hash('sha256', $rawCode);

        LoginCodeTelemetry::record('issued', 'untrusted-source-' . $rawCode);
        LoginCodeTelemetry::record('untrusted-event-' . $digest, 'main-api-issue');

        $messages = array_slice($logger->messages, $before);
        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertSame('Rejected invalid login-code telemetry dimensions.', $message[0]);
            $this->assertSame('login-code', $message[2]);
            $serialized = json_encode($message, JSON_THROW_ON_ERROR);
            $this->assertStringNotContainsString($rawCode, $serialized);
            $this->assertStringNotContainsString($digest, $serialized);
            $this->assertStringNotContainsString('424242', $serialized);
            $this->assertStringNotContainsString('token-secret-' . str_repeat('b', 48), $serialized);
        }
    }
}
