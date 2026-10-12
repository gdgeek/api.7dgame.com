<?php

namespace tests\unit\services;

use api\modules\v1\models\User;
use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityProviderClient;
use common\components\IdentityBackendUser;
use common\components\security\ServiceUnavailableHttpException;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\UnauthorizedHttpException;

class IdentityBackendUserTest extends TestCase
{
    private $environment;
    private array $components;
    private array $cookies;
    private $originalApplication;
    private BackendSession $session;
    private BackendClient $client;
    private BackendAuthority $authority;

    protected function setUp(): void
    {
        $this->environment = getenv('IDENTITY_DATA_SOURCE');
        putenv('IDENTITY_DATA_SOURCE=identity');
        $this->originalApplication = Yii::$app;
        new \yii\web\Application(['id' => 'backend-session-test', 'basePath' => $this->originalApplication->basePath]);
        $this->components = array_intersect_key(Yii::$app->getComponents(), array_flip(['session', 'response', 'request', 'identityAuthority']));
        $this->cookies = $_COOKIE;
        $_COOKIE = [];
        Yii::$app->set('session', $this->session = new BackendSession());
        Yii::$app->set('response', new \yii\web\Response());
        Yii::$app->set('request', new \yii\web\Request(['cookieValidationKey' => 'test-only-cookie-secret', 'hostInfo' => 'http://localhost', 'scriptUrl' => '']));
        Yii::$app->set('identityAuthority', $this->authority = new BackendAuthority());
        $this->client = new BackendClient();
    }

    protected function tearDown(): void
    {
        putenv($this->environment === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $this->environment);
        $_COOKIE = $this->cookies;
        foreach (['session', 'response', 'request', 'identityAuthority'] as $id) Yii::$app->set($id, $this->components[$id] ?? null);
        Yii::$app = $this->originalApplication;
    }

    public function testSignedLegacyCookieExchangesOnceWithoutRenewingCookie(): void
    {
        $name = '_identity-backend';
        $_COOKIE[$name] = Yii::$app->security->hashData(serialize([$name, json_encode([7, 'old-auth-key', 3600])]), 'test-only-cookie-secret');
        $user = $this->user();
        $this->assertSame(7, $user->getIdentity()->id);
        $this->assertSame([[7, 'old-auth-key']], $this->client->exchanges);
        $this->assertSame('access', $this->session->values['__identity_backend_tokens']['accessToken']);
        $this->assertFalse(Yii::$app->response->cookies->has($name));
        $this->assertSame(7, $this->user()->getIdentity()->id);
        $this->assertCount(1, $this->client->exchanges);
    }

    public function testNewRememberLoginKeepsTokensOnlyInServerSession(): void
    {
        $user = $this->user();
        $this->assertTrue($user->loginWithIdentityTokens(['accessToken' => 'access', 'refreshToken' => 'refresh'], 3600));
        $this->assertSame('refresh', $this->session->values['__identity_backend_tokens']['refreshToken']);
        $this->assertFalse(Yii::$app->response->cookies->has('_identity-backend'));
        $cookie = Yii::$app->response->cookies->get($this->session->getName());
        $this->assertNotNull($cookie);
        $this->assertStringNotContainsString('refresh', $cookie->value);
        $this->assertLessThanOrEqual(time() + 3600, $this->session->values['__identity_backend_absolute_expires']);
    }

    public function testUnsignedOrTamperedCookieCannotReachInternalExchange(): void
    {
        $_COOKIE['_identity-backend'] = json_encode([7, 'old-auth-key', 3600]);
        $this->assertNull($this->user()->getIdentity());
        $this->assertSame([], $this->client->exchanges);
    }

    public function testServerSessionRequiresItsOriginalAuthKey(): void
    {
        $this->session->values['__id'] = 7;
        $this->assertNull($this->user()->getIdentity());
        $this->assertSame([], $this->client->exchanges);
        $this->session->values['__authKey'] = 'server-session-auth-key';
        $this->assertSame(7, $this->user()->getIdentity()->id);
        $this->assertSame([[7, 'server-session-auth-key']], $this->client->exchanges);
    }

    public function testExpiredAccessRefreshesOnlyAtIdentity(): void
    {
        $this->session->values['__identity_backend_tokens'] = ['accessToken' => 'expired', 'refreshToken' => 'refresh'];
        $this->assertSame(7, $this->user()->getIdentity()->id);
        $this->assertSame(['refresh'], $this->client->refreshes);
        $this->assertSame('rotated-access', $this->session->values['__identity_backend_tokens']['accessToken']);
    }

    public function testRevokedSessionCannotBecomeLocalLogin(): void
    {
        $this->session->values['__identity_backend_tokens'] = ['accessToken' => 'expired', 'refreshToken' => 'revoked'];
        $this->assertNull($this->user()->getIdentity());
        $this->assertArrayNotHasKey('__identity_backend_tokens', $this->session->values);
    }

    public function testIdentityTransportFailurePropagatesWithoutLocalLogin(): void
    {
        $tokens = ['accessToken' => 'unavailable', 'refreshToken' => 'refresh'];
        $this->session->values['__identity_backend_tokens'] = $tokens;
        try {
            $this->user()->getIdentity();
            $this->fail('An unavailable authority must fail the request.');
        } catch (ServiceUnavailableHttpException $exception) {
            $this->assertSame($tokens, $this->session->values['__identity_backend_tokens']);
            $this->assertSame([], $this->client->refreshes);
        }
    }

    public function testLogoutRevokesIdentityRefreshFamily(): void
    {
        $this->session->values['__identity_backend_tokens'] = ['accessToken' => 'access', 'refreshToken' => 'refresh'];
        $user = $this->user();
        $user->getIdentity();
        $this->assertTrue($user->logout(false));
        $this->assertSame(['refresh'], $this->client->logouts);
        $this->assertArrayNotHasKey('__identity_backend_tokens', $this->session->values);
    }

    public function testDirectLogoutCannotExchangeSurvivingSignedLegacyCookie(): void
    {
        $name = '_identity-backend';
        $_COOKIE[$name] = Yii::$app->security->hashData(serialize([$name, json_encode([7, 'old-auth-key', 3600])]), 'test-only-cookie-secret');
        $this->session->values['__identity_backend_tokens'] = ['accessToken' => 'access', 'refreshToken' => 'refresh'];
        $this->session->values['__identity_backend_absolute_expires'] = time() + 3600;
        $user = $this->user();
        $this->assertTrue($user->logout(false));
        $this->assertSame([], $this->client->exchanges);
        $this->assertSame(['refresh'], $this->client->logouts);
        $this->assertNull($user->getIdentity());
        $this->assertArrayNotHasKey('__identity_backend_absolute_expires', $this->session->values);
    }

    public function testRefreshCompletedAfterLogoutCannotRestoreRevokedSession(): void
    {
        $this->session->values['__identity_backend_tokens'] = ['accessToken' => 'expired', 'refreshToken' => 'refresh'];
        $this->client->afterRefresh = function () {
            // Identity revokes the whole family even when a refresh already
            // produced a successor. The PHP adapter verifies that successor.
            $this->user()->logout(false);
            $this->authority->revoked = true;
        };
        $this->assertNull($this->user()->getIdentity());
        $this->assertSame(['refresh'], $this->client->logouts);
        $this->assertArrayNotHasKey('__identity_backend_tokens', $this->session->values);
    }

    private function user(): IdentityBackendUser
    {
        return new IdentityBackendUser(['identityClass' => User::class, 'identityClient' => $this->client,
            'enableAutoLogin' => true, 'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true]]);
    }
}

class BackendSession extends \yii\web\Session
{
    public array $values = [];
    public function init() {}
    public function get($key, $default = null) { return $this->values[$key] ?? $default; }
    public function set($key, $value) { $this->values[$key] = $value; }
    public function remove($key) { $value = $this->values[$key] ?? null; unset($this->values[$key]); return $value; }
    public function regenerateID($deleteOldSession = false) {}
}

class BackendMemoryUser extends User
{
    public function attributes() { return ['id', 'username', 'status']; }
}

class BackendAuthority extends IdentityAuthorityService
{
    public bool $revoked = false;
    public function verifyAccessToken(string $token): User
    {
        if ($this->revoked) throw new UnauthorizedHttpException('revoked');
        if ($token === 'expired') throw new UnauthorizedHttpException('expired');
        if ($token === 'unavailable') throw new ServiceUnavailableHttpException('unavailable');
        $user = new BackendMemoryUser(); $user->id = 7; $user->username = 'user'; $user->status = 10;
        return $user;
    }
}

class BackendClient extends IdentityProviderClient
{
    public $afterRefresh;
    public array $exchanges = [];
    public array $refreshes = [];
    public array $logouts = [];
    public function legacyPhpSession(int $userId, string $authKey, array $context = []): array
    {
        $this->exchanges[] = [$userId, $authKey];
        return ['accessToken' => 'access', 'refreshToken' => 'refresh'];
    }
    public function refresh(string $refreshToken, array $context = []): array
    {
        $this->refreshes[] = $refreshToken;
        if ($refreshToken === 'revoked') throw new UnauthorizedHttpException('revoked');
        if ($this->afterRefresh !== null) ($this->afterRefresh)();
        return ['accessToken' => 'rotated-access', 'refreshToken' => 'rotated-refresh'];
    }
    public function logout(?string $refreshToken, array $context = []): bool { $this->logouts[] = $refreshToken; return true; }
}
