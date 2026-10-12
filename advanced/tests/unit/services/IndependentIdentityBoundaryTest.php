<?php

namespace tests\unit\services;

use api\modules\v1\filters\IdentityJwtHttpBearerAuth;
use api\modules\v1\models\User;
use api\modules\v1\services\AccountLifecycleProxyService;
use api\modules\v1\services\AppleIdentityProvider;
use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\IdentityRouteGateway;
use api\modules\v1\services\IdentityService;
use api\modules\v1\services\IndependentIdentity;
use api\modules\v1\services\SessionService;
use api\modules\v1\services\UserManagementService;
use common\components\IdentityDbManager;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Request;
use common\components\security\ServiceUnavailableHttpException;
use yii\web\UnauthorizedHttpException;

/** Behavioral boundary tests. These intentionally need no MySQL or Redis. */
class IndependentIdentityBoundaryTest extends TestCase
{
    private array $environment = [];
    private $originalRequest;
    private $originalAuthority;
    private $originalMethod;
    private $originalUser;
    private $originalResponse;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['IDENTITY_DATA_SOURCE', 'IDENTITY_INDEPENDENT_READY', 'AUTH_PROVIDER', 'IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK',
            'ENABLE_APPLE_LOGIN', 'IDENTITY_APPLE_CLIENT_IDS', 'IDENTITY_APPLE_REDIRECT_URIS', 'IDENTITY_APPLE_KEY_ENV_NAMES'] as $key) {
            $this->environment[$key] = getenv($key);
        }
        putenv('IDENTITY_DATA_SOURCE=identity');
        putenv('AUTH_PROVIDER=legacy');
        putenv('IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK=true');
        putenv('ENABLE_APPLE_LOGIN=true');
        putenv('IDENTITY_APPLE_CLIENT_IDS=com.example.app');
        putenv('IDENTITY_APPLE_REDIRECT_URIS=https://example.test/apple-callback');
        putenv('IDENTITY_APPLE_KEY_ENV_NAMES=APPLE_KEY_ID');
        $this->originalRequest = Yii::$app->get('request');
        $this->originalUser = Yii::$app->get('user', false);
        $this->originalResponse = Yii::$app->get('response', false);
        Yii::$app->set('user', new \yii\web\User(['identityClass' => User::class, 'enableSession' => false]));
        Yii::$app->set('response', new \yii\web\Response());
        $this->originalAuthority = Yii::$app->has('identityAuthority') ? Yii::$app->get('identityAuthority') : null;
        $this->originalMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->set('request', new Request(['hostInfo' => 'http://localhost', 'scriptUrl' => '']));
    }

    protected function tearDown(): void
    {
        foreach ($this->environment as $key => $value) {
            putenv($value === false ? $key : $key . '=' . $value);
        }
        Yii::$app->set('request', $this->originalRequest);
        Yii::$app->set('user', $this->originalUser);
        Yii::$app->set('response', $this->originalResponse);
        Yii::$app->set('identityAuthority', $this->originalAuthority);
        if ($this->originalMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->originalMethod;
        }
        parent::tearDown();
    }

    public function testInvalidOwnershipConfigurationCannotSilentlySelectLegacy(): void
    {
        putenv('IDENTITY_DATA_SOURCE=identitty');
        $this->expectException(InvalidConfigException::class);
        IndependentIdentity::enabled();
    }

    public function testIndependentHttpCutoverIsBlockedByDefault(): void
    {
        putenv('IDENTITY_INDEPENDENT_READY=false');
        $this->expectException(InvalidConfigException::class);
        IndependentIdentity::assertHttpReady();
    }

    public function testProjectionCliParsesTheDocumentedLimitOption(): void
    {
        $controller = new BoundaryProjectionController('identity-projection', Yii::$app);
        $this->assertSame(0, $controller->runAction('sync', ['limit' => '17']));
        $this->assertSame(17, $controller->observedLimit);
    }

    public function testProjectionWorkerBacksOffAndStopsWithoutLoggingSensitiveException(): void
    {
        $controller = new BoundaryProjectionController('identity-projection', Yii::$app);
        $controller->watching = true;
        $controller->maxBackoff = 1;
        $this->assertSame(0, $controller->actionWatch());
        $this->assertSame([1], $controller->waits);
        $this->assertStringNotContainsString('SQL secret', $controller->output);
        $this->assertStringContainsString('retrying with backoff', $controller->output);
        $this->assertStringContainsString('worker stopped', $controller->output);
    }

    public function testProjectionWorkerRejectsInvalidLimitBeforeRetryLoop(): void
    {
        $controller = new BoundaryProjectionController('identity-projection', Yii::$app);
        $controller->limit = 0;
        $this->expectException(\yii\console\Exception::class);
        $controller->actionWatch();
    }

    public function testReadinessGateDoesNotChangeLegacyMode(): void
    {
        putenv('IDENTITY_DATA_SOURCE=legacy');
        putenv('IDENTITY_INDEPENDENT_READY=false');
        IndependentIdentity::assertHttpReady();
        $this->assertFalse(IndependentIdentity::enabled());
    }

    public function testLegacyPasswordRouteUsesIdentityPayload(): void
    {
        $client = new BoundaryRecordingClient();
        Yii::$app->request->setBodyParams(['oldPassword' => 'old-secret', 'password' => 'new-secret']);
        (new IdentityRouteGateway(['client' => $client]))->forward('server/reset-password');
        $this->assertSame('/v1/password/change', $client->request['path']);
        $this->assertSame(['old_password' => 'old-secret', 'new_password' => 'new-secret', 'confirm_password' => 'new-secret'], $client->request['payload']);
    }

    public function testQrGatewayPreservesBrowserOriginAndRefererForAuthorityValidation(): void
    {
        $client = new BoundaryRecordingClient();
        Yii::$app->request->headers->set('Origin', 'https://WhiteLabel.example:443');
        Yii::$app->request->headers->set('Referer', 'https://WhiteLabel.example/path?display=qr');
        (new IdentityRouteGateway(['client' => $client]))->forward('v1/tools/user-linked');
        $this->assertSame('/v1/tools/user-linked', $client->request['path']);
        $this->assertSame('https://WhiteLabel.example:443', $client->request['context']['origin']);
        $this->assertSame('https://WhiteLabel.example/path?display=qr', $client->request['context']['referer']);
        $this->assertArrayNotHasKey('aud', $client->request['context']);
        $this->assertArrayNotHasKey('frontend_domain', $client->request['payload']);
    }

    public function testMailTransportDiagnosticKeepsItsProviderOnlyImplementation(): void
    {
        $this->assertNull((new IdentityRouteGateway())->target('v1/email/test'));
    }

    public function testResetTokenProbeIsPublicOnlyInExplicitIndependentMode(): void
    {
        $controller = (new \ReflectionClass(\api\modules\v1\controllers\PasswordController::class))->newInstanceWithoutConstructor();
        $this->assertContains('verify-token', $controller->behaviors()['authenticator']['except']);
        $this->assertContains('verify-token', $controller->behaviors()['access']['allowActions']);
        putenv('IDENTITY_DATA_SOURCE=legacy');
        $this->assertNotContains('verify-token', $controller->behaviors()['authenticator']['except']);
    }

    public function testOldRestLoginKeepsStringAuthAndCurrentIdentityRoles(): void
    {
        $client = new BoundaryLegacyLoginClient();
        $client->response = ['status' => 200, 'body' => ['token' => ['accessToken' => 'identity-access']]];
        Yii::$app->request->setBodyParams(['username' => 'user', 'password' => 'password']);
        $gateway = new IdentityRouteGateway(['client' => $client]);
        $this->assertSame(['nickname' => 'Current', 'username' => 'user', 'roles' => ['manager'], 'auth' => 'identity-access'],
            $gateway->forward('v1/site/login')['body']);
        $this->assertSame('identity-access', $gateway->forward('site/login')['body']['token']);
        $this->assertSame('/v1/auth/login', $client->request['path']);
    }

    public function testIndependentLoginIgnoresLegacyProviderAndPropagatesFailure(): void
    {
        $service = new IdentityService();
        $property = new \ReflectionProperty(IdentityService::class, 'identityProviderClient');
        $property->setValue($service, new BoundaryUnavailableClient());
        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('identity unavailable');
        $service->login('user', 'password');
    }

    public function testIndependentRefreshNeverReadsLegacyRefreshTokens(): void
    {
        $service = new IdentityService();
        $property = new \ReflectionProperty(IdentityService::class, 'identityProviderClient');
        $property->setValue($service, new BoundaryUnavailableClient());
        $this->expectExceptionMessage('identity unavailable');
        $this->expectException(ServiceUnavailableHttpException::class);
        $service->refresh('old-refresh');
    }

    public function testIndependentLogoutCannotFallBackToLocalRedis(): void
    {
        $service = new IdentityService();
        $property = new \ReflectionProperty(IdentityService::class, 'identityProviderClient');
        $property->setValue($service, new BoundaryUnavailableClient());
        $this->expectException(ServiceUnavailableHttpException::class);
        $service->logout('old-refresh');
    }

    public function testBrowserMigrationMarkerCannotSelectLegacyLifecycleWriter(): void
    {
        Yii::$app->request->headers->set('X-Identity-Lifecycle-Proxy', '1');
        $this->assertTrue((new AccountLifecycleProxyService())->shouldProxy('password'));
    }

    public function testGatewayPreservesIdentityErrorsWithoutExecutingLocalWriter(): void
    {
        $client = new BoundaryRecordingClient();
        $client->response = ['status' => 503, 'body' => ['code' => 'IDENTITY_UNAVAILABLE']];
        $gateway = new IdentityRouteGateway(['client' => $client]);
        Yii::$app->request->setBodyParams(['id' => 42, 'password' => 'new-password']);
        Yii::$app->request->headers->set('Authorization', 'Bearer retained-access-token');
        Yii::$app->request->headers->set('X-Identity-Lifecycle-Proxy', '1');
        $this->assertSame($client->response, $gateway->forward('v1/plugin-user/update-user'));
        $this->assertSame('/v1/plugin-user/update-user', $client->request['path']);
        $this->assertSame('Bearer retained-access-token', $client->request['context']['authorization']);
        $this->assertArrayNotHasKey('X-Identity-Lifecycle-Proxy', $client->request['context']);
    }

    public function testLegacyPersonRoleChangePreservesIdentityCompatibilityContract(): void
    {
        $client = new BoundaryRecordingClient();
        $gateway = new IdentityRouteGateway(['client' => $client]);
        Yii::$app->request->setBodyParams(['id' => 42, 'auth' => 'manager']);
        $gateway->forward('v1/person/auth');
        $this->assertSame('/v1/person/auth', $client->request['path']);
        $this->assertSame(['id' => 42, 'auth' => 'manager'], $client->request['payload']);
    }

    public function testAppleOnlyForwardsVerifiedProviderClaimsToIdentity(): void
    {
        $client = new BoundaryAppleClient();
        $provider = new BoundaryAppleProvider(['client' => $client]);
        $provider->forward('apple-id', $this->appleRequest());
        $this->assertSame('resolve', $client->request['operation']);
        $this->assertSame('verified-subject', $client->request['payload']['subject']);
        $this->assertSame('com.example.app', $client->request['payload']['providerAppId']);
        $this->assertSame('verified@example.test', $client->request['payload']['email']);
        $this->assertArrayNotHasKey('code', $client->request['payload']);
        $this->assertArrayNotHasKey('token', $client->request['payload']);
    }

    public function testAppleUnconfiguredClientCannotReachProviderExchange(): void
    {
        $body = $this->appleRequest();
        $body['data']['userData']['aud'] = 'attacker-app';
        $provider = new BoundaryAppleProvider();
        try {
            $provider->forward('apple-id', $body);
            $this->fail('Unconfigured Apple audience must be rejected.');
        } catch (UnauthorizedHttpException $exception) {
            $this->assertSame(0, $provider->exchanges);
        }
    }

    public function testAppleVerifiedSubjectStillRequiresMatchingIssuerAndAudience(): void
    {
        $provider = new BoundaryAppleProvider();
        $provider->claimsOverride = ['aud' => 'another-app'];
        $this->expectException(UnauthorizedHttpException::class);
        $provider->forward('apple-id', $this->appleRequest());
    }

    public function testAppleCreateCannotForwardClientForgedVerifiedClaims(): void
    {
        $client = new BoundaryAppleClient();
        (new AppleIdentityProvider(['client' => $client]))->forward('apple-id-create', [
            'apple_id' => 'subject', 'token' => 'identity-challenge', 'username' => 'user', 'password' => 'secret',
            'emailVerified' => true, 'email' => 'forged@example.test', 'user_id' => 42,
        ]);
        $this->assertSame('create', $client->request['operation']);
        $this->assertSame(['apple_id', 'token', 'username', 'password'], array_keys($client->request['payload']));
    }

    public function testAppleUnverifiedEmailCannotBecomeAnAccountMergeSignal(): void
    {
        $client = new BoundaryAppleClient();
        $provider = new BoundaryAppleProvider(['client' => $client]);
        $provider->claimsOverride = ['email_verified' => false];
        $provider->forward('apple-id', $this->appleRequest());
        $this->assertNull($client->request['payload']['email']);
        $this->assertFalse($client->request['payload']['emailVerified']);
    }

    public function testAppleFeatureDisablePreservesExistingResponse(): void
    {
        putenv('ENABLE_APPLE_LOGIN=false');
        $provider = new BoundaryAppleProvider();
        $result = $provider->forward('apple-id', $this->appleRequest());
        $this->assertSame(501, $result['status']);
        $this->assertSame('FEATURE_DISABLED', $result['body']['code']);
        $this->assertSame(0, $provider->exchanges);
    }

    private function appleRequest(): array
    {
        return ['data' => ['userData' => ['aud' => 'com.example.app', 'sub' => 'untrusted-client-subject'],
            'authorization' => ['code' => 'provider-authorization-code']],
            'url' => 'https://example.test/apple-callback', 'key' => 'APPLE_KEY_ID'];
    }

    public function testPersonRestActionIdsPreserveQueryAndHttpVerb(): void
    {
        $client = new BoundaryRecordingClient();
        $client->response = ['status' => 200, 'body' => []];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Yii::$app->request->setQueryParams(['page' => 2, 'expand' => 'organizations']);
        $gateway = new IdentityRouteGateway(['client' => $client]);
        $gateway->forward('v1/person/index');
        $this->assertSame('/v1/person', $client->request['path']);
        $this->assertSame('GET', $client->request['method']);
        $this->assertSame(['page' => 2, 'expand' => 'organizations'], $client->request['query']);
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        Yii::$app->request->setQueryParams(['id' => 42]);
        Yii::$app->request->setBodyParams(['nickname' => 'Changed']);
        $gateway->forward('v1/person/update');
        $this->assertSame('/v1/person/42', $client->request['path']);
        $this->assertSame('PUT', $client->request['method']);
        $this->assertSame(['nickname' => 'Changed'], $client->request['payload']);
    }

    public function testPersonListAggregatesBusinessAvatarsInOneRead(): void
    {
        $client = new BoundaryRecordingClient();
        $client->response = ['status' => 200, 'body' => [
            ['id' => 42, 'avatar_id' => 8, 'avatar' => null],
            ['id' => 43, 'avatar_id' => null, 'avatar' => ['id' => 'stale']],
            ['id' => 44, 'avatar_id' => 9, 'avatar' => null],
            ['id' => 45, 'avatar_id' => 8, 'avatar' => null],
        ], 'headers' => ['X-Pagination-Total-Count' => '4']];
        $gateway = new BoundaryAvatarGateway(['client' => $client]);
        $result = $gateway->forward('v1/person/index');
        $this->assertSame([8, 9], $gateway->requestedIds);
        $this->assertSame(['id' => 8, 'url' => 'http://localhost/avatar'], $result['body'][0]['avatar']);
        $this->assertNull($result['body'][1]['avatar']);
        $this->assertNull($result['body'][2]['avatar']);
        $this->assertArrayNotHasKey('avatar_id', $result['body'][0]);
        $this->assertSame('4', $result['headers']['X-Pagination-Total-Count']);
    }

    public function testBusinessActionsRemainLocalAndLegacyModeDoesNotProxy(): void
    {
        $gateway = new IdentityRouteGateway();
        $this->assertNull($gateway->target('v1/verse/update'));
        $this->assertNull($gateway->target('v1/plugin-campus/clear-content'));
        $this->assertNull($gateway->target('wechat/pay'));
        $this->assertNull($gateway->target('wechat/pay-notify'));
        $this->assertNull($gateway->target('v1/user/creation'));
        putenv('IDENTITY_DATA_SOURCE=legacy');
        $this->assertNull($gateway->forward('v1/plugin-user/create-user'));
    }

    public function testCurrentProfilePreservesBusinessBalancesAndClearsRemovedAvatar(): void
    {
        $client = new BoundarySubjectClient();
        $client->subject['userInfo'] = ['description' => 'Identity profile'];
        $client->subject['avatarId'] = null;
        $client->subject['permissions'] = ['user-management.list-users'];
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => $client]));
        $user = new \stdClass();
        $user->id = 42;
        $user->userInfo = new BoundaryBusinessProfile();
        $data = (new UserManagementService())->buildCurrentUserPayload($user);
        $this->assertSame(['username' => 'user', 'nickname' => null], $data['userData']);
        $this->assertSame(['description' => 'Identity profile'], $data['userInfo']['info']);
        $this->assertSame(50, $data['userInfo']['gold']);
        $this->assertSame(8, $data['userInfo']['points']);
        $this->assertNull($data['userInfo']['avatar']);
        $this->assertSame(['user-management.list-users'], $data['perms']);
    }

    public function testProfileUpdateReturnsTheExistingCompleteAggregatedShape(): void
    {
        $subjectClient = new BoundarySubjectClient();
        $subjectClient->subject['userInfo'] = ['description' => 'New profile'];
        Yii::$app->set('identityAuthority', new BoundaryMemoryAuthority(['client' => $subjectClient]));
        Yii::$app->request->setBodyParams(['nickname' => 'Changed']);
        Yii::$app->request->headers->set('Authorization', 'Bearer retained-access-token');
        $gatewayClient = new BoundaryRecordingClient();
        $result = (new IdentityRouteGateway(['client' => $gatewayClient]))->forward('v1/user/update');
        $this->assertTrue($result['body']['success']);
        $this->assertSame('ok', $result['body']['message']);
        $this->assertSame(['user'], $result['body']['data']['roles']);
        $this->assertSame(50, $result['body']['data']['userInfo']['gold']);
        $this->assertSame(['description' => 'New profile'], $result['body']['data']['userInfo']['info']);
    }

    public function testCurrentRolesComeFromIdentityWithoutReadingLocalAssignments(): void
    {
        $authority = new IdentityAuthorityService(['client' => new BoundarySubjectClient()]);
        Yii::$app->set('identityAuthority', $authority);
        $manager = new IdentityDbManager(['db' => Yii::$app->db]);
        $this->assertSame(['user'], array_keys($manager->getRolesByUser(42)));
        $this->assertArrayNotHasKey('root', $manager->getAssignments(42));
        $this->assertSame(['user'], array_keys($manager->getAssignments(42)));
    }

    public function testInvalidSubjectBindingIsRejected(): void
    {
        $authority = new IdentityAuthorityService(['client' => new BoundarySubjectClient()]);
        $this->expectException(ServiceUnavailableHttpException::class);
        $authority->subject(43);
    }

    public function testCampusTargetsUseCurrentIdentityMembershipAndRoles(): void
    {
        $client = new BoundaryMembersClient();
        Yii::$app->set('identityAuthority', new BoundaryMemoryAuthority(['client' => $client]));
        $actor = new BoundaryMemoryUser();
        $actor->id = 42;
        $controller = new \api\modules\v1\controllers\PluginCampusController('plugin-campus', Yii::$app);
        $method = new \ReflectionMethod($controller, 'findManageableUsers');
        $users = $method->invoke($controller, $actor, ['manager'], [17], '');
        $this->assertSame([99], array_map(static fn(User $user): int => (int)$user->id, $users));
        $this->assertSame(0, $client->fullSubjectReads);
        $this->assertSame(1, $client->membershipReads);
        $users = $method->invoke($controller, $actor, ['manager'], [17], 'does-not-match');
        $this->assertSame([], $users);
        $this->assertSame(1, $client->membershipReads);
    }

    public function testRemovedCampusManagerCannotUseStaleBusinessMembership(): void
    {
        Yii::$app->set('identityAuthority', new BoundaryMemoryAuthority(['client' => new BoundaryMembersClient()]));
        $actor = new BoundaryMemoryUser();
        $actor->id = 42;
        $controller = new \api\modules\v1\controllers\PluginCampusController('plugin-campus', Yii::$app);
        $response = Yii::$app->get('response');
        Yii::$app->set('response', new \yii\web\Response());
        try {
            $result = (new \ReflectionMethod($controller, 'assertOrganizationAllowed'))->invoke($controller, $actor, ['manager'], 17);
            $this->assertSame(2003, $result['code']);
            $this->assertSame(403, Yii::$app->response->statusCode);
        } finally {
            Yii::$app->set('response', $response);
        }
    }

    public function testMembershipSummaryCannotEraseFullDirectPermissionAssignments(): void
    {
        $client = new BoundaryMembersClient();
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => $client]));
        IndependentIdentity::authority()->organizationMembers(17);
        $manager = new IdentityDbManager();
        $this->assertSame(['user'], array_keys($manager->getRolesByUser(99)));
        $this->assertSame(0, $client->fullSubjectReads);
        $this->assertArrayHasKey('direct-object-permission', $manager->getAssignments(99));
        $this->assertSame(1, $client->fullSubjectReads);
    }

    public function testIdentityAllowCannotBypassBusinessObjectDenial(): void
    {
        $client = new BoundarySubjectClient();
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => $client]));
        $manager = new BoundaryBusinessRuleManager();
        $manager->businessAllowed = false;
        $this->assertFalse($manager->checkAccess(42, 'updateVerse', ['id' => 7]));
        $this->assertSame(1, $manager->businessChecks);
    }

    public function testIdentityDenialCannotBeOverruledByBusinessObjectAllow(): void
    {
        $client = new BoundarySubjectClient();
        $client->allowed = false;
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => $client]));
        $manager = new BoundaryBusinessRuleManager();
        $manager->businessAllowed = true;
        $this->assertFalse($manager->checkAccess(42, 'updateVerse', ['id' => 7]));
        $this->assertSame(0, $manager->businessChecks);
    }

    public function testGlobalAccessDoesNotDependOnLegacyPermissionAssignments(): void
    {
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => new BoundarySubjectClient()]));
        $manager = new BoundaryBusinessRuleManager();
        $manager->hasRule = false;
        $manager->businessAllowed = false;
        $this->assertTrue($manager->checkAccess(42, 'user-management.list-users'));
        $this->assertSame(0, $manager->businessChecks);
    }

    public function testDisabledIdentityCannotReachProjectionOrAuthenticate(): void
    {
        $client = new BoundarySubjectClient();
        $client->subject['status'] = 0;
        $authority = new IdentityAuthorityService(['client' => $client]);
        $this->expectException(UnauthorizedHttpException::class);
        $authority->verifyAccessToken('valid-but-disabled.jwt');
    }

    public function testRejectedRetainedJwtCannotUseLocalUserOrEmbeddedRoles(): void
    {
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => new BoundaryUnavailableClient()]));
        $auth = new IdentityJwtHttpBearerAuth(['throwException' => false]);
        Yii::$app->request->headers->set('Authorization', 'Bearer retained.jwt');
        $this->expectException(ServiceUnavailableHttpException::class);
        $auth->authenticate(Yii::$app->user, Yii::$app->request, new \yii\web\Response());
    }

    public function testDirectLocalPasswordMutationFailsBeforeDatabaseAccess(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        (new User())->setPassword('password');
    }

    public function testDirectLegacyTokenIssuanceFailsBeforeWritingRefreshToken(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        (new SessionService())->issueToken(new User());
    }

    public function testBulkLegacyAccountMutationCannotBypassActiveRecordHooks(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        User::updateAll(['status' => 10], ['id' => 42]);
    }

    public function testLegacyMembershipDeleteCannotBypassIdentityOwnership(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        \api\modules\v1\models\UserOrganization::deleteAll(['user_id' => 42]);
    }

    public function testLegacyInvitationCreationCannotRemainASecondWriter(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        (new \common\models\Invitation())->beforeSave(true);
    }

    public function testDirectLocalPolicyItemMutationIsDisabled(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        (new \common\models\AuthItem())->beforeSave(true);
    }
}

class BoundaryRecordingClient extends IdentityProviderClient
{
    public array $request = [];
    public array $response = ['status' => 200, 'body' => ['success' => true]];

    public function proxyAccountLifecycle(string $method, string $path, ?array $payload = null, array $query = [], array $context = []): array
    {
        $this->request = compact('method', 'path', 'payload', 'query', 'context');
        return $this->response;
    }
}

class BoundaryLegacyLoginClient extends BoundaryRecordingClient
{
    public function verifyIdentityAccessToken(string $token): array
    {
        return ['id' => 42, 'username' => 'user', 'nickname' => 'Current', 'status' => 10, 'roles' => ['manager']];
    }
}

class BoundaryProjectionController extends \console\controllers\IdentityProjectionController
{
    public int $observedLimit = 0;
    public bool $watching = false;
    public array $waits = [];
    public string $output = '';
    private int $calls = 0;
    protected function syncPage(): array
    {
        $this->observedLimit = $this->limit;
        ++$this->calls;
        if ($this->watching && $this->calls === 1) {
            throw new \RuntimeException('SQL secret and credentials must never be logged');
        }
        if ($this->watching) {
            $this->stopRequested = true;
        }
        return ['applied' => 2, 'received' => $this->limit, 'cursor' => 10];
    }
    protected function installSignals(): void {}
    protected function waitForNextPage(int $seconds): void { $this->waits[] = $seconds; }
    public function stdout($string) { $this->output .= $string; }
    public function stderr($string) { $this->output .= $string; }
}

class BoundaryAppleClient extends IdentityProviderClient
{
    public array $request = [];
    public function verifiedApple(string $operation, array $payload, array $context = []): array
    {
        $this->request = compact('operation', 'payload', 'context');
        return ['status' => 200, 'body' => ['apple_id' => 'verified-subject', 'token' => 'identity-challenge']];
    }
}

class BoundaryAppleProvider extends AppleIdentityProvider
{
    public int $exchanges = 0;
    public array $claimsOverride = [];
    protected function exchangeCode(string $code, array $configuration): array
    {
        ++$this->exchanges;
        return ['subject' => 'verified-subject', 'firstName' => 'Profile name', 'claims' => array_merge([
            'iss' => 'https://appleid.apple.com', 'aud' => 'com.example.app', 'sub' => 'verified-subject',
            'exp' => time() + 60, 'iat' => time(), 'email_verified' => true, 'email' => 'verified@example.test',
        ], $this->claimsOverride)];
    }
}

class BoundaryAvatarGateway extends IdentityRouteGateway
{
    public array $requestedIds = [];

    protected function loadPersonAvatars(array $ids): array
    {
        $this->requestedIds = $ids;
        return [8 => ['id' => 8, 'url' => 'http://localhost/avatar']];
    }
}

class BoundaryUnavailableClient extends IdentityProviderClient
{
    public function login(string $username, string $password, array $context = []): array
    {
        throw new ServiceUnavailableHttpException('identity unavailable');
    }

    public function refresh(string $refreshToken, array $context = []): array
    {
        throw new ServiceUnavailableHttpException('identity unavailable');
    }

    public function logout(?string $refreshToken, array $context = []): bool
    {
        throw new ServiceUnavailableHttpException('identity unavailable');
    }

    public function verifyIdentityAccessToken(string $token): array
    {
        throw new ServiceUnavailableHttpException('identity unavailable');
    }
}

class BoundarySubjectClient extends IdentityProviderClient
{
    public bool $allowed = true;
    public array $subject = ['id' => 42, 'username' => 'user', 'status' => 10, 'roles' => ['user'], 'organizations' => [], 'permissions' => []];

    public function identitySubject(int $id): array
    {
        return $this->subject;
    }

    public function verifyIdentityAccessToken(string $token): array
    {
        return $this->subject;
    }

    public function identityPermission(int $id, string $permission): bool
    {
        return $this->allowed;
    }
}

class BoundaryBusinessRuleManager extends IdentityDbManager
{
    public bool $hasRule = true;
    public bool $businessAllowed = false;
    public int $businessChecks = 0;

    protected function hasBusinessRule(string $permissionName): bool
    {
        return $this->hasRule;
    }

    protected function evaluateBusinessRuleAccess($userId, $permissionName, $params): bool
    {
        ++$this->businessChecks;
        return $this->businessAllowed;
    }
}

class BoundaryBusinessProfile
{
    public function toArray(): array
    {
        return ['gold' => 50, 'points' => 8, 'info' => 'old-profile', 'avatar' => ['id' => 88]];
    }
}

class BoundaryMemoryUser extends User
{
    public $userInfo;

    public function attributes()
    {
        return ['id', 'username', 'nickname', 'email', 'email_verified_at', 'status'];
    }
}

class BoundaryMemoryAuthority extends IdentityAuthorityService
{
    public function projectSubject(array $subject): User
    {
        $user = new BoundaryMemoryUser();
        $user->id = $subject['id'];
        $user->username = $subject['username'];
        $user->userInfo = new BoundaryBusinessProfile();
        return $user;
    }
}

class BoundaryMembersClient extends BoundarySubjectClient
{
    public int $fullSubjectReads = 0;
    public int $membershipReads = 0;

    public function identityOrganizationMembers(int $id): array
    {
        ++$this->membershipReads;
        return ['organization' => ['id' => 17, 'name' => 'campus', 'title' => 'Campus'], 'subjects' => [
            ['id' => 99, 'username' => 'student', 'status' => 10, 'roles' => ['user'], 'organizations' => [['id' => 17]]],
            ['id' => 100, 'username' => 'admin', 'status' => 10, 'roles' => ['admin'], 'organizations' => [['id' => 17]]],
            ['id' => 101, 'username' => 'disabled', 'status' => 0, 'roles' => ['user'], 'organizations' => [['id' => 17]]],
        ]];
    }

    public function identitySubject(int $id): array
    {
        ++$this->fullSubjectReads;
        return array_merge($this->subject, ['id' => $id, 'directPermissions' => ['direct-object-permission']]);
    }
}
