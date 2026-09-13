<?php

namespace tests\unit\webmcp;

use PHPUnit\Framework\TestCase;

final class PublicationConnectionConfigTest extends TestCase
{
    public function testBusinessDatabaseConnectionCanTransportUnescapedFourByteUnicode(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/common/config/main-local.php';
        $this->assertSame('utf8mb4', $config['components']['db']['charset']);
    }
}
