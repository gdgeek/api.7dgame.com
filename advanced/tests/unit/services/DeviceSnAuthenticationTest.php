<?php

namespace tests\unit\services;

use api\modules\v1\components\DeviceSnAuthContext;
use api\modules\v1\exceptions\DeviceSnAuthenticationException;
use api\modules\v1\models\User;
use api\modules\v1\RefreshToken;
use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\IdentityService;
use api\modules\v1\services\SessionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\web\UnauthorizedHttpException;

final class DeviceSnAuthenticationTest extends TestCase
{
    private string|false $previousProvider;
    private array $originalComponents = [];
    private array $accessDatabases = [];
    private mixed $originalConfigs;

    protected function setUp(): void
    {
        $this->previousProvider = getenv('AUTH_PROVIDER');
        putenv('AUTH_PROVIDER=legacy');
    }

    protected function tearDown(): void
    {
        if ($this->originalComponents !== []) {
            (new \ReflectionProperty(\mdm\admin\components\Configs::class, '_instance'))->setValue(null, $this->originalConfigs);
        }
        foreach ($this->originalComponents as $id => $component) {
            Yii::$app->set($id, $component);
        }
        foreach ($this->accessDatabases as $db) {
            $db->close();
        }
        putenv($this->previousProvider === false ? 'AUTH_PROVIDER' : 'AUTH_PROVIDER=' . $this->previousProvider);
    }

    public function testRefreshRotationsPreserveDeviceSourceAndStopAfterDisablement(): void
    {
        $user = $this->user();
        $sessions = new MemoryDeviceSessionService($user);
        $identity = new DeviceIdentityService($sessions);
        $token = $identity->issueUserToken($user, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);

        for ($i = 0; $i < 3; $i++) {
            $claims = Yii::$app->jwt->parse($token['accessToken'])->claims();
            $this->assertSame(['auth_method' => 'device_sn', 'device_sn_id' => 9], DeviceSnAuthContext::fromClaims($claims));
            $this->assertLessThanOrEqual(10800, $claims->get('exp')->getTimestamp() - $claims->get('iat')->getTimestamp());
            $this->assertSame(1, count($sessions->records));
            $previous = $token['refreshToken'];
            $token = $identity->refresh($previous, ['auth_method' => 'password', 'device_sn_id' => 999]);
            $this->assertNotSame($previous, $token['refreshToken']);
            $this->assertNull($sessions->findRefreshTokenRecord($previous));
        }

        $sessions->enabled = false;
        $this->expectException(DeviceSnAuthenticationException::class);
        $identity->refresh($token['refreshToken']);
    }

    #[DataProvider('invalidContexts')]
    public function testPartialDeviceSourceCannotBecomeAnOrdinarySession(array $context): void
    {
        $this->expectException(DeviceSnAuthenticationException::class);
        DeviceSnAuthContext::normalize($context);
    }

    public static function invalidContexts(): array
    {
        return [
            [['auth_method' => 'device_sn']],
            [['device_sn_id' => 9]],
            [['auth_method' => 'password', 'device_sn_id' => 9]],
            [['auth_method' => 'device_sn', 'device_sn_id' => 0]],
            [['auth_method' => 'device_sn', 'device_sn_id' => 'bad']],
        ];
    }

    public function testOldRefreshAttributesRemainOrdinary(): void
    {
        $this->assertSame([], DeviceSnAuthContext::normalize([]));
        $this->assertSame([], DeviceSnAuthContext::normalize(['auth_method' => null, 'device_sn_id' => null]));
        $this->assertSame(['auth_method' => 'device_sn', 'device_sn_id' => 9],
            DeviceSnAuthContext::normalize(['auth_method' => 'device_sn', 'device_sn_id' => '9']));
    }

    #[DataProvider('invalidJwtSources')]
    public function testSignedJwtWithIncompleteSourceCannotAuthenticateAsOrdinary(array $source): void
    {
        $builder = Yii::$app->jwt->getBuilder();
        foreach ($source as $name => $value) {
            $builder = $builder->withClaim($name, $value);
        }
        $configuration = Yii::$app->jwt->getConfiguration();
        $signed = $builder->getToken($configuration->signer(), $configuration->signingKey());
        $this->expectException(DeviceSnAuthenticationException::class);
        DeviceSnAuthContext::fromClaims(Yii::$app->jwt->parse($signed->toString())->claims());
    }

    public static function invalidJwtSources(): array
    {
        return [
            [['auth_method' => null, 'device_sn_id' => null]],
            [['device_sn_id' => null]],
            [['auth_method' => 'device_sn', 'device_sn_id' => null]],
            [['auth_method' => 'password', 'device_sn_id' => 9]],
        ];
    }

    public function testIdentityFailureDoesNotFallBackToLocalIssuerForDeviceLogin(): void
    {
        putenv('AUTH_PROVIDER=identity');
        $user = $this->user();
        $sessions = new MemoryDeviceSessionService($user);
        $identity = new DeviceIdentityService($sessions, new FailingDeviceProvider());
        try {
            $identity->issueUserToken($user, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
            $this->fail('Expected provider failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('issuer unavailable', $exception->getMessage());
        }
        $this->assertSame([], $sessions->records);
    }

    public function testOrdinaryIssuanceKeepsLegacyFallback(): void
    {
        putenv('AUTH_PROVIDER=identity');
        $user = $this->user();
        $sessions = new MemoryDeviceSessionService($user);
        $identity = new DeviceIdentityService($sessions, new FailingDeviceProvider());
        $token = $identity->issueUserToken($user);
        $this->assertNotEmpty($token['refreshToken']);
        $this->assertSame([], DeviceSnAuthContext::fromClaims(Yii::$app->jwt->parse($token['accessToken'])->claims()));
    }

    public function testIdentityDeviceDenialDoesNotTryLegacyOrLoginCodeFallback(): void
    {
        putenv('AUTH_PROVIDER=identity');
        $sessions = new MemoryDeviceSessionService($this->user());
        $identity = new DeviceIdentityService($sessions, new FailingDeviceProvider());
        try {
            $identity->refresh('device-refresh');
            $this->fail('Expected device denial.');
        } catch (DeviceSnAuthenticationException) {
            $this->assertSame(0, $sessions->lookups);
        }
    }

    public function testProviderForwardsSourceAndAcceptsMatchingSignedToken(): void
    {
        $user = $this->user();
        $context = ['auth_method' => 'device_sn', 'device_sn_id' => 9];
        $provider = new RecordingDeviceProvider([
            'accessToken' => $user->generateAccessToken(null, null, null, null, $context),
            'refreshToken' => 'refresh', 'expires' => 'unused',
        ]);
        $previous = getenv('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN');
        putenv('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN=test-internal-token');
        try {
            $token = $provider->issueUserToken(42, $context);
            $this->assertSame(['legacyUserId' => 42] + $context, $provider->payload);
            $this->assertNotEmpty($token['accessToken']);
        } finally {
            putenv($previous === false ? 'IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN' : 'IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN=' . $previous);
        }
    }

    public function testOlderIssuerDroppingDeviceClaimsIsRejected(): void
    {
        $token = ['accessToken' => $this->user()->generateAccessToken(), 'refreshToken' => 'refresh'];
        $this->expectException(DeviceSnAuthenticationException::class);
        (new IdentityProviderClient())->assertDeviceToken($token, 42, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
    }

    public function testIssuerCannotSubstituteAnotherBindingOrUser(): void
    {
        $token = ['accessToken' => $this->user()->generateAccessToken(null, null, null, null,
            ['auth_method' => 'device_sn', 'device_sn_id' => 10]), 'refreshToken' => 'refresh'];
        $this->expectException(DeviceSnAuthenticationException::class);
        (new IdentityProviderClient())->assertDeviceToken($token, 42, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
    }

    public function testDeviceAccessLifetimeIsCappedWithoutChangingOrdinaryLifetime(): void
    {
        $user = $this->user();
        $now = new \DateTimeImmutable();
        $token = $user->generateAccessToken($now, $now->modify('+1 day'), null, null,
            ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
        $this->assertSame(10800, Yii::$app->jwt->parse($token)->claims()->get('exp')->getTimestamp() - $now->getTimestamp());
    }

    public function testExistingDeviceAccessSurvivesOrdinarySnDisablementAndPreservesSource(): void
    {
        $db = $this->prepareAccessDatabases();
        $token = $this->deviceAccessToken();
        $db->createCommand()->update('device_sn', ['enabled' => 0], ['id' => 9])->execute();

        $identity = User::findIdentityByAccessToken($token);
        $this->assertSame(42, (int)$identity->id);
        $this->assertSame(['auth_method' => 'device_sn', 'device_sn_id' => 9], $identity->authContext);
    }

    #[DataProvider('deletedAccountScenarios')]
    public function testDeletedBindingCannotAuthenticateThroughStaleReplicaOrReusedUserId(bool $recreateUser): void
    {
        $db = $this->prepareAccessDatabases(true);
        $token = $this->deviceAccessToken();
        $db->createCommand()->delete('user', ['id' => 42])->execute();
        $binding = $db->createCommand('SELECT user_id, original_user_id, device_uuid FROM device_sn WHERE id = 9')->queryOne();
        $this->assertNull($binding['user_id']);
        $this->assertSame(42, (int)$binding['original_user_id']);
        $this->assertSame('rokid-one', $binding['device_uuid']);
        $this->assertNotNull(User::findIdentity(42), 'The ordinary identity database still contains the deleted user.');
        if ($recreateUser) {
            $db->createCommand()->insert('user', ['id' => 42, 'username' => 'replacement-user', 'status' => 10])->execute();
        }

        $this->expectException(UnauthorizedHttpException::class);
        User::findIdentityByAccessToken($token);
    }

    public static function deletedAccountScenarios(): array
    {
        return ['stale replica' => [false], 'same user ID recreated' => [true]];
    }

    #[DataProvider('ineligibleDeviceAccounts')]
    public function testExistingDeviceAccessRechecksAuthoritativeAccountStatusAndRoles(int $status, ?string $role): void
    {
        $db = $this->prepareAccessDatabases(true);
        $token = $this->deviceAccessToken();
        $db->createCommand()->update('user', ['status' => $status], ['id' => 42])->execute();
        if ($role !== null) {
            $db->createCommand()->insert('auth_assignment', ['item_name' => $role, 'user_id' => '42'])->execute();
        }
        $this->assertSame(10, (int)User::findIdentity(42)->status, 'The ordinary database has not received the account change.');

        $this->expectException(UnauthorizedHttpException::class);
        User::findIdentityByAccessToken($token);
    }

    public static function ineligibleDeviceAccounts(): array
    {
        return ['disabled account' => [0, null], 'root' => [10, 'root'], 'admin' => [10, 'admin'], 'manager' => [10, 'manager']];
    }

    #[DataProvider('elevatedDefaultRoleViews')]
    public function testDefaultRbacElevationCannotBroadenAnOrdinaryDeviceSession(bool $cached): void
    {
        $this->prepareAccessDatabases(true);
        $token = $this->deviceAccessToken();
        Yii::$app->db->createCommand()->insert('auth_assignment', ['item_name' => 'admin', 'user_id' => '42'])->execute();
        if ($cached) {
            $this->assertArrayHasKey('admin', Yii::$app->authManager->getRolesByUser(42));
            Yii::$app->db->createCommand()->delete('auth_assignment', ['item_name' => 'admin', 'user_id' => '42'])->execute();
            $this->assertSame(['user'], Yii::$app->db->createCommand('SELECT item_name FROM auth_assignment WHERE user_id = :id', [':id' => '42'])->queryColumn());
        }

        $this->expectException(UnauthorizedHttpException::class);
        User::findIdentityByAccessToken($token);
    }

    public static function elevatedDefaultRoleViews(): array
    {
        return ['stale default database' => [false], 'stale default RBAC cache' => [true]];
    }

    #[DataProvider('invalidDeviceAccessBindings')]
    public function testExistingDeviceAccessRequiresItsOriginalActivatedBinding(?array $changes): void
    {
        $db = $this->prepareAccessDatabases();
        $token = $this->deviceAccessToken();
        if ($changes === null) {
            $db->createCommand()->delete('device_sn', ['id' => 9])->execute();
        } else {
            $db->createCommand()->update('device_sn', $changes, ['id' => 9])->execute();
        }

        $this->expectException(UnauthorizedHttpException::class);
        User::findIdentityByAccessToken($token);
    }

    public static function invalidDeviceAccessBindings(): array
    {
        return [
            'missing SN' => [null],
            'different account' => [['user_id' => 43]],
            'not activated' => [['activated_at' => null]],
            'invalid UUID' => [['device_uuid' => 'bad uuid']],
        ];
    }

    public function testDeviceAccessReturnsAuthoritativeIdentityWithoutDependingOnReplica(): void
    {
        $this->prepareAccessDatabases(true);
        $token = $this->deviceAccessToken();
        Yii::$app->db->createCommand()->delete('user', ['id' => 42])->execute();
        $this->assertNull(User::findIdentity(42));

        $identity = User::findIdentityByAccessToken($token);
        $this->assertSame('device-user', $identity->username);
        $this->assertSame(42, (int)$identity->id);
    }

    public function testOrdinaryPasswordAccessKeepsExistingIdentityLookupWithoutSnDatabase(): void
    {
        $db = $this->prepareAccessDatabases();
        $db->createCommand()->update('user', ['status' => 0], ['id' => 42])->execute();
        Yii::$app->set('deviceSnDb', null);

        $identity = User::findIdentityByAccessToken($this->user()->generateAccessToken());
        $this->assertSame(42, (int)$identity->id);
        $this->assertSame(0, (int)$identity->status);
        $this->assertSame([], $identity->authContext);
    }

    public function testMalformedRefreshRecordDoesNotRenew(): void
    {
        $user = $this->user();
        $sessions = new MemoryDeviceSessionService($user);
        $token = $sessions->issueToken($user, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
        $sessions->findRefreshTokenRecord($token['refreshToken'])->auth_method = null;
        $this->expectException(DeviceSnAuthenticationException::class);
        (new DeviceIdentityService($sessions))->refresh($token['refreshToken']);
    }

    private function user(): DeviceAuthTestUser
    {
        $user = new DeviceAuthTestUser();
        $user->id = 42;
        $user->username = 'device-user';
        $user->status = 10;
        return $user;
    }

    private function deviceAccessToken(): string
    {
        return $this->user()->generateAccessToken(null, null, null, null, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
    }

    private function prepareAccessDatabases(bool $separateReplica = false): Connection
    {
        $configs = new \ReflectionProperty(\mdm\admin\components\Configs::class, '_instance');
        $this->originalConfigs = $configs->getValue();
        $configs->setValue(null, null);
        foreach (['db', 'deviceSnDb', 'authManager'] as $id) {
            $this->originalComponents[$id] = Yii::$app->get($id, false);
        }
        $primary = $this->newAccessDatabase();
        Yii::$app->set('deviceSnDb', $primary);
        Yii::$app->set('db', $separateReplica ? $this->newAccessDatabase() : $primary);
        Yii::$app->set('authManager', new DbManager(['db' => 'db', 'cache' => new ArrayCache()]));
        return $primary;
    }

    private function newAccessDatabase(): Connection
    {
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        $this->accessDatabases[] = $db;
        foreach ([
            'PRAGMA foreign_keys = ON',
            'CREATE TABLE user (id INTEGER PRIMARY KEY, username TEXT, status INTEGER)',
            'CREATE TABLE auth_item (name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER)',
            'CREATE TABLE auth_assignment (item_name TEXT, user_id TEXT)',
            'CREATE TABLE device_sn (id INTEGER PRIMARY KEY, user_id INTEGER NULL REFERENCES user(id) ON DELETE SET NULL, original_user_id INTEGER NOT NULL, device_uuid TEXT UNIQUE, activated_at TEXT, enabled INTEGER)',
        ] as $sql) {
            $db->createCommand($sql)->execute();
        }
        $db->createCommand()->batchInsert('user', ['id', 'username', 'status'], [[42, 'device-user', 10], [43, 'other-user', 10]])->execute();
        $db->createCommand()->batchInsert('auth_item', ['name', 'type'], [['user', 1], ['admin', 1], ['root', 1], ['manager', 1]])->execute();
        $db->createCommand()->insert('auth_assignment', ['item_name' => 'user', 'user_id' => '42'])->execute();
        $db->createCommand()->insert('device_sn', ['id' => 9, 'user_id' => 42, 'original_user_id' => 42,
            'device_uuid' => 'rokid-one', 'activated_at' => '2026-09-28 00:00:00', 'enabled' => 1])->execute();
        return $db;
    }
}

class DeviceAuthTestUser extends User
{
    public function attributes(): array { return ['id', 'username', 'status']; }
    public function validate($attributeNames = null, $clearErrors = true): bool { return true; }
    public function save($runValidation = true, $attributeNames = null): bool { return true; }
}

final class DeviceIdentityService extends IdentityService
{
    public function __construct(private SessionService $sessions, private ?IdentityProviderClient $provider = null)
    { parent::__construct(); }
    public function sessionService(): SessionService { return $this->sessions; }
    public function identityProviderClient(): IdentityProviderClient { return $this->provider ?? parent::identityProviderClient(); }
}

final class FailingDeviceProvider extends IdentityProviderClient
{
    public function issueUserToken(int $legacyUserId, array $context = []): array { throw new \RuntimeException('issuer unavailable'); }
    public function refresh(string $refreshToken, array $context = []): array { throw new DeviceSnAuthenticationException(); }
}

final class RecordingDeviceProvider extends IdentityProviderClient
{
    public array $payload = [];
    public function __construct(private array $token) { parent::__construct(); }
    protected function postJson(string $path, array $payload, array $context): array
    { $this->payload = $payload; return ['token' => $this->token]; }
}

final class MemoryDeviceSessionService extends SessionService
{
    public array $records = [];
    public bool $enabled = true;
    public int $lookups = 0;
    public function __construct(private User $testUser) { parent::__construct(); }
    protected function newRefreshToken(): RefreshToken { return new MemoryDeviceRefreshRecord($this); }
    public function findRefreshTokenRecord(string $refreshToken): ?RefreshToken
    { $this->lookups++; return $this->records[RefreshToken::hashToken($refreshToken)] ?? null; }
    protected function authorizeDeviceSession(int $snId, int $userId): User
    {
        if (!$this->enabled || $snId !== 9 || $userId !== 42) { throw new DeviceSnAuthenticationException(); }
        return $this->testUser;
    }
}

final class MemoryDeviceRefreshRecord extends RefreshToken
{
    public function __construct(private MemoryDeviceSessionService $store) { parent::__construct(); }
    public function save($runValidation = true, $attributeNames = null): bool
    { $this->store->records[$this->key] = $this; return true; }
    public function delete(): int
    { unset($this->store->records[$this->key]); return 1; }
}
