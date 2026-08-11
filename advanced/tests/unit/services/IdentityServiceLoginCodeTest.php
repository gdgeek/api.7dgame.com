<?php

namespace tests\unit\services;

use api\modules\v1\models\User;
use api\modules\v1\services\IdentityService;
use api\modules\v1\services\LoginCodeReadiness;
use api\modules\v1\services\LoginCodeSettings;
use api\modules\v1\services\LoginCodeStore;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\web\UnauthorizedHttpException;

final class IdentityServiceLoginCodeTest extends TestCase
{
    private mixed $originalDb;
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDb = Yii::$app->get('db', false);
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $this->db);
        $this->db->open();
        $this->db->createCommand()->createTable('{{%user}}', [
            'id' => 'integer primary key',
        ])->execute();
        $this->db->createCommand()->insert('{{%user}}', ['id' => 42])->execute();
    }

    protected function tearDown(): void
    {
        $this->db->close();
        Yii::$app->set('db', $this->originalDb);

        parent::tearDown();
    }

    public function testSameQrLoginCodeCanBeRedeemedRepeatedlyWithinItsActiveWindow(): void
    {
        $store = $this->storeForCodes(str_repeat('a', 64));
        $code = $store->issue(42)['key'];
        $service = new RecordingIdentityService($store);

        $firstToken = $service->refresh('web_' . $code, ['session_id' => 'first-redemption']);
        $secondToken = $service->refresh('web_' . $code, ['session_id' => 'second-redemption']);

        $this->assertNotSame($firstToken['refreshToken'], $secondToken['refreshToken']);
        $this->assertSame([
            ['user_id' => 42, 'context' => ['session_id' => 'first-redemption']],
            ['user_id' => 42, 'context' => ['session_id' => 'second-redemption']],
        ], $service->issuedForUsers);
    }

    public function testSameUserCanRedeemMultipleDistinctActiveQrLoginCodes(): void
    {
        $store = $this->storeForCodes(str_repeat('b', 64), str_repeat('c', 64));
        $firstCode = $store->issue(42)['key'];
        $secondCode = $store->issue(42)['key'];
        $service = new RecordingIdentityService($store);

        $firstToken = $service->refresh('web_' . $firstCode, ['session_id' => 'first-code']);
        $secondToken = $service->refresh('web_' . $secondCode, ['session_id' => 'second-code']);

        $this->assertNotSame($firstCode, $secondCode);
        $this->assertNotSame($firstToken['refreshToken'], $secondToken['refreshToken']);
        $this->assertSame([
            ['user_id' => 42, 'context' => ['session_id' => 'first-code']],
            ['user_id' => 42, 'context' => ['session_id' => 'second-code']],
        ], $service->issuedForUsers);
    }

    public function testBareCodeFallsBackToTheLoginCodeStoreAfterLegacyRefreshRejectsIt(): void
    {
        $store = $this->storeForCodes(str_repeat('d', 64));
        $code = $store->issue(42)['key'];
        $legacyRefresh = new RejectingLoginCodeSessionService();
        $service = new RecordingIdentityService($store, $legacyRefresh);

        $token = $service->refresh($code, ['session_id' => 'bare-code-fallback']);

        $this->assertSame([$code], $legacyRefresh->attemptedTokens);
        $this->assertSame('refresh-token-1', $token['refreshToken']);
        $this->assertSame([
            ['user_id' => 42, 'context' => ['session_id' => 'bare-code-fallback']],
        ], $service->issuedForUsers);
    }

    public function testWebPrefixedUnknownCodeDoesNotFallBackToLegacyRefresh(): void
    {
        $store = $this->storeForCodes();
        $legacyRefresh = new RejectingLoginCodeSessionService();
        $service = new RecordingIdentityService($store, $legacyRefresh);

        try {
            $service->refresh('web_' . str_repeat('e', 64));
            $this->fail('Expected an invalid login code to be rejected.');
        } catch (UnauthorizedHttpException $exception) {
            $this->assertSame('Login code is invalid or expired.', $exception->getMessage());
        }

        $this->assertSame([], $legacyRefresh->attemptedTokens);
        $this->assertSame([], $service->issuedForUsers);
    }

    private function storeForCodes(string ...$codes): LoginCodeStore
    {
        $redis = new IdentityServiceLoginCodeRedis(1780000000000);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:identity-login-code:v1',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'clock' => static fn (): int => $redis->nowMilliseconds(),
        ]);

        return new LoginCodeStore(
            $redis,
            $settings,
            $readiness,
            static function () use (&$codes): string {
                return array_shift($codes) ?? '';
            }
        );
    }
}

/**
 * Records the user selected by the real IdentityService consumer while keeping
 * token issuance independent from the refresh_token persistence path.
 */
final class RecordingIdentityService extends IdentityService
{
    /** @var list<array{user_id: int, context: array<string, mixed>}> */
    public array $issuedForUsers = [];

    public function __construct(
        private LoginCodeStore $testLoginCodeStore,
        private ?RejectingLoginCodeSessionService $testSessionService = null,
        array $config = []
    )
    {
        parent::__construct($config);
    }

    public function loginCodeStore(): LoginCodeStore
    {
        return $this->testLoginCodeStore;
    }

    public function issueUserToken(User $user, array $context = []): array
    {
        $this->issuedForUsers[] = [
            'user_id' => (int)$user->id,
            'context' => $context,
        ];
        $sequence = count($this->issuedForUsers);

        return [
            'accessToken' => 'access-token-' . $sequence,
            'expires' => '2030-01-01 00:00:00',
            'refreshToken' => 'refresh-token-' . $sequence,
        ];
    }

    public function sessionService(): \api\modules\v1\services\SessionService
    {
        return $this->testSessionService ?? parent::sessionService();
    }
}

final class RejectingLoginCodeSessionService extends \api\modules\v1\services\SessionService
{
    /** @var list<string> */
    public array $attemptedTokens = [];

    public function consumeRefreshToken(string $refreshToken): User
    {
        $this->attemptedTokens[] = $refreshToken;
        throw new UnauthorizedHttpException('Refresh token is invalid.');
    }
}

/**
 * Minimal exact-key Redis test adapter. It deliberately has no consume or
 * delete-on-read behaviour, so the test exercises the real Redis Code_Record
 * resolution path used by IdentityService.
 */
final class IdentityServiceLoginCodeRedis
{
    /** @var array<string, array{payload: string, pttl: int}> */
    private array $records = [];

    public function __construct(private int $nowMilliseconds)
    {
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function executeCommand(string $command, array $arguments)
    {
        return match (strtoupper($command)) {
            'TIME' => [
                (string)intdiv($this->nowMilliseconds, 1000),
                (string)(($this->nowMilliseconds % 1000) * 1000),
            ],
            'SET' => $this->set($arguments),
            'GET' => $this->records[(string)$arguments[0]]['payload'] ?? null,
            'PTTL' => $this->records[(string)$arguments[0]]['pttl'] ?? -2,
            default => throw new \RuntimeException('Unexpected Redis command: ' . $command),
        };
    }

    public function nowMilliseconds(): int
    {
        return $this->nowMilliseconds;
    }

    /** @param array<int, mixed> $arguments */
    private function set(array $arguments): ?string
    {
        $key = (string)$arguments[0];
        if (isset($this->records[$key])) {
            return null;
        }

        $this->records[$key] = [
            'payload' => (string)$arguments[1],
            'pttl' => (int)$arguments[3],
        ];

        return 'OK';
    }
}
