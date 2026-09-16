<?php

namespace tests\integration\fixtures;

use yii\web\IdentityInterface;

/** Test-only HTTP dependencies. Never loads application credentials or JWT keys. */
final class WebMcpHttpFixture
{
    public static function assertTestDatabase(\yii\db\Connection $db): void
    {
        preg_match('/(?:^|;)dbname=(webmcp_test_[a-z0-9_]+)(?:;|$)/D', $db->dsn, $match);
        if (!isset($match[1]) || $db->createCommand('SELECT DATABASE()')->queryScalar() !== $match[1]) {
            throw new \RuntimeException('Connected database does not match the isolated test DSN');
        }
    }

    public static function databaseConfig(): array
    {
        $dsn = getenv('WEBMCP_MYSQL_TEST_DSN') ?: '';
        if (!str_starts_with($dsn, 'mysql:')) {
            throw new \RuntimeException('An explicit loopback MySQL test DSN is required');
        }
        $parts = [];
        foreach (explode(';', substr($dsn, 6)) as $part) {
            if ($part === '') continue;
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            if (isset($parts[$key]) || !in_array($key, ['host', 'port', 'dbname', 'charset'], true)) {
                throw new \RuntimeException('Unsupported or duplicate test DSN parameter');
            }
            $parts[$key] = $value;
        }
        if (!in_array($parts['host'] ?? '', ['127.0.0.1', 'localhost', '::1'], true)
            || !preg_match('/\Awebmcp_test_[a-z0-9_]+\z/D', $parts['dbname'] ?? '')
            || (isset($parts['port']) && (!ctype_digit($parts['port']) || (int) $parts['port'] < 1 || (int) $parts['port'] > 65535))
            || (isset($parts['charset']) && !in_array($parts['charset'], ['utf8', 'utf8mb4'], true))) {
            throw new \RuntimeException('Refusing a database outside loopback webmcp_test_*');
        }
        return [
            'class' => \yii\db\Connection::class,
            'dsn' => $dsn,
            'username' => getenv('WEBMCP_MYSQL_TEST_USER') ?: 'root',
            'password' => getenv('WEBMCP_MYSQL_TEST_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'enableLogging' => false,
            'enableProfiling' => false,
        ];
    }
}

final class WebMcpHttpIdentity implements IdentityInterface
{
    public function __construct(public int $id) {}
    public static function findIdentity($id): ?self { return new self((int) $id); }
    public static function findIdentityByAccessToken($token, $type = null): ?self { return null; }
    public function getId(): int { return $this->id; }
    public function getAuthKey(): string { return ''; }
    public function validateAuthKey($authKey): bool { return false; }
}
