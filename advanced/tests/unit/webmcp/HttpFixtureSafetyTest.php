<?php

namespace tests\unit\webmcp;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use tests\integration\fixtures\WebMcpHttpFixture;

require_once dirname(__DIR__, 2) . '/integration/fixtures/WebMcpHttpFixture.php';

final class HttpFixtureSafetyTest extends TestCase
{
    public static function unsafeDsns(): array
    {
        return [
            'application database' => ['mysql:host=127.0.0.1;dbname=bujiaban'],
            'development database' => ['mysql:host=127.0.0.1;dbname=bujiaban_development'],
            'remote database' => ['mysql:host=db.example.com;dbname=webmcp_test_ci'],
            'socket override' => ['mysql:host=127.0.0.1;dbname=webmcp_test_ci;unix_socket=/tmp/mysql.sock'],
            'duplicate database' => ['mysql:host=127.0.0.1;dbname=webmcp_test_ci;dbname=bujiaban'],
            'duplicate host' => ['mysql:host=127.0.0.1;dbname=webmcp_test_ci;host=db.example.com'],
            'invalid database suffix' => ['mysql:host=127.0.0.1;dbname=webmcp_test_ci-extra'],
            'missing host' => ['mysql:dbname=webmcp_test_ci'],
            'invalid port' => ['mysql:host=127.0.0.1;port=70000;dbname=webmcp_test_ci'],
            'other driver' => ['sqlite::memory:'],
        ];
    }

    #[DataProvider('unsafeDsns')]
    public function testRejectsUnsafeDsnBeforeConnecting(string $dsn): void
    {
        $original = getenv('WEBMCP_MYSQL_TEST_DSN');
        putenv('WEBMCP_MYSQL_TEST_DSN=' . $dsn);
        try {
            $this->expectException(\RuntimeException::class);
            WebMcpHttpFixture::databaseConfig();
        } finally {
            $original === false ? putenv('WEBMCP_MYSQL_TEST_DSN') : putenv('WEBMCP_MYSQL_TEST_DSN=' . $original);
        }
    }

    public function testAcceptsExplicitLoopbackDisposableDatabaseWithoutConnecting(): void
    {
        $original = getenv('WEBMCP_MYSQL_TEST_DSN');
        $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=webmcp_test_ci;charset=utf8mb4';
        putenv('WEBMCP_MYSQL_TEST_DSN=' . $dsn);
        try {
            $this->assertSame($dsn, WebMcpHttpFixture::databaseConfig()['dsn']);
        } finally {
            $original === false ? putenv('WEBMCP_MYSQL_TEST_DSN') : putenv('WEBMCP_MYSQL_TEST_DSN=' . $original);
        }
    }
}
