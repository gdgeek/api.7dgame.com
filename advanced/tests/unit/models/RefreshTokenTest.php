<?php

namespace tests\unit\models;

use api\modules\v1\RefreshToken;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    public function testHashTokenIsDeterministicSha256(): void
    {
        $rawToken = 'raw-refresh-token';

        $hash = RefreshToken::hashToken($rawToken);

        $this->assertSame(hash('sha256', $rawToken), $hash);
        $this->assertNotSame($rawToken, $hash);
        $this->assertSame(64, strlen($hash));
    }

    public function testExpirySecondsIsPositive(): void
    {
        $this->assertGreaterThan(0, RefreshToken::expirySeconds());
    }

    public function testIsExpiredDetectsPastAndFutureExpiry(): void
    {
        $expiredToken = new RefreshToken();
        $expiredToken->expires_at = time() - 1;

        $activeToken = new RefreshToken();
        $activeToken->expires_at = time() + 60;

        $this->assertTrue($expiredToken->isExpired());
        $this->assertFalse($activeToken->isExpired());
    }
}
