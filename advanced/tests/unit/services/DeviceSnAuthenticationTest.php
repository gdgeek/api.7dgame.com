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
use yii\web\UnauthorizedHttpException;

final class DeviceSnAuthenticationTest extends TestCase
{
    private string|false $previousProvider;

    protected function setUp(): void
    {
        $this->previousProvider = getenv('AUTH_PROVIDER');
        putenv('AUTH_PROVIDER=legacy');
    }

    protected function tearDown(): void
    {
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

    public function testExistingDeviceAccessPreservesSourceAndCannotGainElevatedRoles(): void
    {
        $user = new RoleCheckedDeviceUser();
        $user->id = 42;
        $user->status = 10;
        RoleCheckedDeviceUser::$stored = $user;
        $token = $user->generateAccessToken(null, null, null, null, ['auth_method' => 'device_sn', 'device_sn_id' => 9]);
        // No SN database is configured: ordinary business authentication only
        // rechecks account eligibility, leaving disabled SN access to expire.
        $identity = RoleCheckedDeviceUser::findIdentityByAccessToken($token);
        $this->assertSame(9, $identity->authContext['device_sn_id']);
        $user->testRoles = ['user', 'admin'];
        $this->expectException(UnauthorizedHttpException::class);
        RoleCheckedDeviceUser::findIdentityByAccessToken($token);
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
}

class DeviceAuthTestUser extends User
{
    public function attributes(): array { return ['id', 'username', 'status']; }
    public function validate($attributeNames = null, $clearErrors = true): bool { return true; }
    public function save($runValidation = true, $attributeNames = null): bool { return true; }
}

final class RoleCheckedDeviceUser extends DeviceAuthTestUser
{
    public static ?self $stored = null;
    public array $testRoles = ['user'];
    public static function findIdentity($id) { return self::$stored; }
    public function getRoles() { return $this->testRoles; }
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
