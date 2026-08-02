<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\ToolsController;
use api\modules\v1\models\User;
use api\modules\v1\services\LoginCodeReadiness;
use api\modules\v1\services\LoginCodeSettings;
use api\modules\v1\services\LoginCodeStore;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;
use yii\web\User as WebUser;

/**
 * Behaviour-level contract for the QR login-code controller boundary.
 *
 * The test database deliberately contains no `user_linked` table.  In the
 * Redis-only mode exercised here, issuing and checking a code must therefore
 * be satisfied entirely through LoginCodeStore's Code_Record protocol.
 */
final class ToolsControllerLoginCodeTest extends TestCase
{
    private mixed $originalDb;
    private mixed $originalRequest;
    private mixed $originalUser;
    private string|false $originalCorsAllowedOrigins;
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDb = Yii::$app->get('db', false);
        $this->originalRequest = Yii::$app->get('request', false);
        $this->originalUser = Yii::$app->get('user', false);
        $this->originalCorsAllowedOrigins = getenv('CORS_ALLOWED_ORIGINS');

        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $this->db);
        $this->db->open();
        $this->db->createCommand()->createTable('{{%user}}', [
            'id' => 'integer primary key',
        ])->execute();

        Yii::$app->set('request', new Request());
    }

    protected function tearDown(): void
    {
        $this->db->close();
        Yii::$app->set('user', $this->originalUser);
        Yii::$app->set('request', $this->originalRequest);
        Yii::$app->set('db', $this->originalDb);
        if ($this->originalCorsAllowedOrigins === false) {
            putenv('CORS_ALLOWED_ORIGINS');
        } else {
            putenv('CORS_ALLOWED_ORIGINS=' . $this->originalCorsAllowedOrigins);
        }

        parent::tearDown();
    }

    public function testIssueReturnsTheExistingEnvelopeWithoutAUserLinkedTable(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('a', 64));
        $this->setCurrentUser(42);

        $response = $controller->actionUserLinked();

        $this->assertSame([
            'success' => true,
            'message' => 'user-linked',
            'key' => str_repeat('a', 64),
            'expires_at' => 1780000060,
            'expires_in' => 60,
        ], $response);
    }

    public function testStatusAcceptsWebQueryInputAndDoesNotConsumeTheCode(): void
    {
        [$controller, $redis] = $this->controllerForCode(str_repeat('b', 64));
        $this->setCurrentUser(42);
        $issued = $controller->actionUserLinked();
        $this->setRequestKey('?web_' . $issued['key']);

        $first = $controller->actionUserLinkedStatus();
        $second = $controller->actionUserLinkedStatus();

        $this->assertSame([
            'success' => true,
            'message' => 'user-linked-status',
            'active' => true,
            'reason' => 'active',
            'expires_at' => 1780000060,
            'expires_in' => 60,
        ], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $redis->commandCount('SET'));
        $this->assertSame(0, $redis->commandCount('DEL'));
    }

    public function testIssueStoresTrustedFrontendOriginAsDomainMetadata(): void
    {
        [$controller, $redis] = $this->controllerForCode(str_repeat('f', 64));
        $this->setCurrentUser(42);
        putenv('CORS_ALLOWED_ORIGINS=https://d.dev.xrugc.com,https://port.xrteeth.com');
        Yii::$app->request->getHeaders()->set('Origin', 'https://D.DEV.XRUGC.COM:443');

        $controller->actionUserLinked();

        $record = json_decode(
            array_values($redis->records())[0]['payload'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $this->assertSame(['frontend_domain' => 'd.dev.xrugc.com'], $record['context']);
    }

    public function testIssueOmitsUntrustedFrontendOrigin(): void
    {
        [$controller, $redis] = $this->controllerForCode(str_repeat('0', 64));
        $this->setCurrentUser(42);
        putenv('CORS_ALLOWED_ORIGINS=https://d.dev.xrugc.com');
        Yii::$app->request->getHeaders()->set('Origin', 'https://attacker.example');

        $controller->actionUserLinked();

        $record = json_decode(
            array_values($redis->records())[0]['payload'],
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $this->assertSame([], $record['context']);
    }

    public function testStatusHidesAnotherUsersCodeAndOmitsExpiryMetadata(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('c', 64));
        $this->setCurrentUser(42);
        $issued = $controller->actionUserLinked();

        $this->setCurrentUser(7);
        $this->setRequestKey('web_' . $issued['key']);

        $this->assertSame([
            'success' => true,
            'message' => 'user-linked-status',
            'active' => false,
            'reason' => 'not_found',
        ], $controller->actionUserLinkedStatus());
    }

    public function testStatusRejectsAnEmptyKeyBeforeStoreResolution(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('d', 64));
        $this->setCurrentUser(42);
        $this->setRequestKey('   ');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('key is required');

        $controller->actionUserLinkedStatus();
    }

    public function testStatusReusesUserLinkedRbacPermission(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('f', 64));
        $this->setCurrentUser(42);
        $this->setRequestKey(str_repeat('f', 64));
        $controller->setCanAccessUserLinked(false);

        $this->expectException(ForbiddenHttpException::class);

        $controller->actionUserLinkedStatus();
    }

    public function testAccessFilterDoesNotRequireASeparateStatusRoute(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('f', 64));

        $this->assertSame(
            ['user-linked-status'],
            $controller->behaviors()['access']['allowActions'] ?? null,
        );
    }

    public function testIssueRejectsANonUserIdentity(): void
    {
        [$controller] = $this->controllerForCode(str_repeat('e', 64));
        Yii::$app->set('user', new WebUser([
            'identityClass' => User::class,
            'enableSession' => false,
        ]));

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid user identity');

        $controller->actionUserLinked();
    }

    /** @return array{0: ToolsControllerLoginCodeTestController, 1: ToolsControllerLoginCodeRedis} */
    private function controllerForCode(string $code): array
    {
        $redis = new ToolsControllerLoginCodeRedis(1780000000000);
        $settings = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_REDIS,
            'writeMode' => LoginCodeSettings::WRITE_REDIS,
            'prefix' => 'test:tools-login-code:v1',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'clock' => static fn (): int => $redis->nowMilliseconds(),
        ]);
        $store = new LoginCodeStore(
            $redis,
            $settings,
            $readiness,
            static fn (): string => $code,
        );

        return [
            new ToolsControllerLoginCodeTestController('tools', Yii::$app->getModule('v1'), $store),
            $redis,
        ];
    }

    private function setCurrentUser(int $id): void
    {
        $identity = new User();
        $identity->id = $id;

        $user = new WebUser([
            'identityClass' => User::class,
            'enableSession' => false,
        ]);
        $user->setIdentity($identity);
        Yii::$app->set('user', $user);
    }

    private function setRequestKey(string $key): void
    {
        $request = new Request();
        $request->setQueryParams(['key' => $key]);
        Yii::$app->set('request', $request);
    }
}

final class ToolsControllerLoginCodeTestController extends ToolsController
{
    private bool $canAccessUserLinked = true;

    public function __construct($id, $module, private LoginCodeStore $testStore, array $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    protected function loginCodeStore(): LoginCodeStore
    {
        return $this->testStore;
    }

    public function setCanAccessUserLinked(bool $canAccess): void
    {
        $this->canAccessUserLinked = $canAccess;
    }

    protected function canAccessUserLinked(): bool
    {
        return $this->canAccessUserLinked;
    }
}

/**
 * Minimal Redis adapter for controller contract coverage. It stores only the
 * digest-addressed values created by LoginCodeStore and deliberately exposes
 * no database or delete-on-read fallback.
 */
final class ToolsControllerLoginCodeRedis
{
    /** @var array<string, array{payload: string, pttl: int}> */
    private array $records = [];
    /** @var array<string, int> */
    private array $commandCounts = [];

    public function __construct(private int $nowMilliseconds)
    {
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function executeCommand(string $command, array $arguments)
    {
        $command = strtoupper($command);
        $this->commandCounts[$command] = ($this->commandCounts[$command] ?? 0) + 1;

        return match ($command) {
            'TIME' => [
                (string)intdiv($this->nowMilliseconds, 1000),
                (string)(($this->nowMilliseconds % 1000) * 1000),
            ],
            'SET' => $this->set($arguments),
            'GET' => $this->records[(string)$arguments[0]]['payload'] ?? null,
            'PTTL' => $this->records[(string)$arguments[0]]['pttl'] ?? -2,
            'DEL' => $this->delete($arguments),
            default => throw new \RuntimeException('Unexpected Redis command: ' . $command),
        };
    }

    public function nowMilliseconds(): int
    {
        return $this->nowMilliseconds;
    }

    public function commandCount(string $command): int
    {
        return $this->commandCounts[strtoupper($command)] ?? 0;
    }

    /** @return array<string, array{payload: string, pttl: int}> */
    public function records(): array
    {
        return $this->records;
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

    /** @param array<int, mixed> $arguments */
    private function delete(array $arguments): int
    {
        $key = (string)$arguments[0];
        if (!isset($this->records[$key])) {
            return 0;
        }

        unset($this->records[$key]);
        return 1;
    }
}
