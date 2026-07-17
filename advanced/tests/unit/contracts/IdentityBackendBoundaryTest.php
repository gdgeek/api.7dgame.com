<?php

namespace tests\unit\contracts;

use PHPUnit\Framework\TestCase;

final class IdentityBackendBoundaryTest extends TestCase
{
    private function path(string $relativePath): string
    {
        return dirname(__DIR__, 3) . '/' . $relativePath;
    }

    private function read(string $relativePath): string
    {
        $path = $this->path($relativePath);
        $this->assertFileExists($path);

        return (string)file_get_contents($path);
    }

    public function testIdentityBoundaryServicesExist(): void
    {
        foreach ([
            'api/modules/v1/services/IdentityService.php',
            'api/modules/v1/services/SessionService.php',
            'api/modules/v1/services/PasswordAccountService.php',
            'api/modules/v1/services/EmailAccountService.php',
            'api/modules/v1/services/UserManagementService.php',
            'api/modules/v1/services/AuthorizationService.php',
            'api/modules/v1/services/LoginAuditReporter.php',
            'api/modules/v1/services/IdentityProviderClient.php',
            'api/modules/v1/services/IamShadowCompareService.php',
            'api/modules/v1/services/AccountLifecycleProxyService.php',
            'api/modules/v1/controllers/InternalIdentityController.php',
        ] as $relativePath) {
            $this->assertFileExists($this->path($relativePath));
        }
    }

    public function testAuthControllerDelegatesToIdentityServiceAndRegistersLogout(): void
    {
        $controller = $this->read('api/modules/v1/controllers/AuthController.php');
        $config = $this->read('../files/api/config/main.php');

        foreach ([
            'IdentityService',
            'identityService()',
            '->login($username, $password',
            '->refresh($refreshToken',
            'public function actionLogout()',
            "'success' => true",
            "'message' => 'logout'",
            "'revoked'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $controller);
        }

        $this->assertStringContainsString("'POST logout' => 'logout'", $config);
        $this->assertStringContainsString("'DELETE logout' => 'logout'", $config);
    }

    public function testSessionServiceKeepsRefreshCompatibilityAndMetadata(): void
    {
        $sessionService = $this->read('api/modules/v1/services/SessionService.php');
        $refreshToken = $this->read('api/modules/v1/RefreshToken.php');
        $internalIdentityController = $this->read('api/modules/v1/controllers/InternalIdentityController.php');
        $config = $this->read('../files/api/config/main.php');
        $params = $this->read('../files/common/config/params.php');

        foreach ([
            'RefreshToken::hashToken($refreshToken)',
            "where(['key' => \$refreshToken])",
            '$token->delete();',
            'revokeUserSessions(int $userId)',
            'revoked_at',
            'contextFromRequest',
        ] as $needle) {
            $this->assertStringContainsString($needle, $sessionService);
        }

        foreach ([
            'session_id',
            'user_agent',
            'ip',
            'created_at',
            'expires_at',
            'revoked_at',
            'isRevoked()',
        ] as $needle) {
            $this->assertStringContainsString($needle, $refreshToken);
        }

        foreach ([
            'X-Identity-Internal-Token',
            'IDENTITY_ACCOUNT_INTERNAL_TOKEN',
            'IDENTITY_INTERNAL_API_TOKEN',
            'revokeUserSessions((int)$legacyUserId)',
            'legacyUserId must be a positive integer.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $internalIdentityController);
        }

        $this->assertStringContainsString("'controller' => 'v1/internal-identity'", $config);
        $this->assertStringContainsString("'POST revoke-sessions' => 'revoke-sessions'", $config);
        $this->assertStringContainsString("'IDENTITY_ACCOUNT_INTERNAL_TOKEN' => getenv('IDENTITY_ACCOUNT_INTERNAL_TOKEN')", $params);
    }

    public function testQrUserLinkedIssuesShortLivedReusableLoginCode(): void
    {
        $toolsController = $this->read('api/modules/v1/controllers/ToolsController.php');
        $identityService = $this->read('api/modules/v1/services/IdentityService.php');
        $identityProviderClient = $this->read('api/modules/v1/services/IdentityProviderClient.php');
        $userLinked = $this->read('api/modules/v1/models/UserLinked.php');
        $apiConfig = $this->read('../files/api/config/main.php');
        $params = $this->read('../files/common/config/params.php');

        foreach ([
            'IdentityService',
            'generateRandomString(64)',
            '$linked->key = RefreshToken::hashToken($loginCode);',
            'UserLinked::LOGIN_CODE_TTL_SECONDS',
            "'key'=> \$loginCode",
            'public function actionUserLinkedStatus()',
            'RefreshToken::hashToken($key)',
            "'active' => \$active",
            "'reason' => \$reason",
            "'expires_at' => \$expiresAt",
            "'expires_in' => max(0, \$expiresAt - time())",
        ] as $needle) {
            $this->assertStringContainsString($needle, $toolsController);
        }

        foreach ([
            '$user->getRefreshToken()->one()',
            '$linked->key = $token->key',
            'sessionService()->issueToken($user, $this->requestContext())',
        ] as $needle) {
            $this->assertStringNotContainsString($needle, $toolsController);
        }

        foreach ([
            'public function issueUserToken(User $user',
            'identityProviderClient()->issueUserToken((int)$user->id',
            'Identity user token issuance failed; issuing legacy token fallback.',
            'sessionService()->issueToken($user',
            'normalizeRefreshTokenInput($refreshToken)',
            "if (\$normalized['from_login_code'])",
            'Login code is invalid or expired.',
            "preg_match('/(?:^|[?&])web_([^&#\\s]+)/'",
            'refreshFromLinkedLoginCode(',
            '$hashedLinkedKey = RefreshToken::hashToken($linkedKey);',
            "UserLinked::find()->where(['key' => \$lookupKeys])->one()",
            '$linked->isLoginCodeExpired()',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityService);
        }

        foreach ([
            'findRefreshTokenRecord($linkedKey)',
            '$refreshToken->isExpired()',
            '$refreshToken->isRevoked()',
            '$linked->key = RefreshToken::hashToken($nextRefreshToken);',
        ] as $needle) {
            $this->assertStringNotContainsString($needle, $identityService);
        }

        foreach ([
            'public const LOGIN_CODE_TTL_SECONDS = 60;',
            'loginCodeExpiresAt()',
            'isLoginCodeExpired()',
        ] as $needle) {
            $this->assertStringContainsString($needle, $userLinked);
        }

        foreach ([
            'public function issueUserToken(int $legacyUserId',
            '/internal/auth/issue-user-token',
            'IDENTITY_INTERNAL_API_TOKEN is required for identity user token issuance.',
            'IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN',
            'IDENTITY_ACCOUNT_INTERNAL_TOKEN',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityProviderClient);
        }

        $this->assertStringContainsString("'GET user-linked/status' => 'user-linked-status'", $apiConfig);
        $this->assertStringContainsString("'IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN' => getenv('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN')", $params);
    }

    public function testJwtClaimsRemainBackwardCompatibleAndAddModernClaims(): void
    {
        $user = $this->read('api/modules/v1/models/User.php');

        foreach ([
            "->withClaim('uid', \$this->id)",
            "->withClaim('session_id', \$sessionId)",
            '->relatedTo((string)$this->id)',
            '->permittedFor((string)$audience)',
            '->identifiedBy((string)$jti)',
            "has('uid')",
            "has('sub')",
        ] as $needle) {
            $this->assertStringContainsString($needle, $user);
        }
    }

    public function testAccountUserAndAuthorizationControllersUseBoundaryServices(): void
    {
        $passwordController = $this->read('api/modules/v1/controllers/PasswordController.php');
        $emailController = $this->read('api/modules/v1/controllers/EmailController.php');
        $userController = $this->read('api/modules/v1/controllers/UserController.php');
        $pluginController = $this->read('api/modules/v1/controllers/PluginController.php');
        $organizationController = $this->read('api/modules/v1/controllers/OrganizationController.php');
        $userManagementService = $this->read('api/modules/v1/services/UserManagementService.php');
        $authorizationService = $this->read('api/modules/v1/services/AuthorizationService.php');
        $passwordService = $this->read('api/modules/v1/services/PasswordResetService.php');

        $this->assertStringContainsString('PasswordAccountService', $passwordController);
        $this->assertStringContainsString('EmailAccountService', $emailController);
        $this->assertStringContainsString('UserManagementService', $userController);
        $this->assertStringContainsString('AuthorizationService', $pluginController);
        $this->assertStringContainsString('IamShadowCompareService', $pluginController);
        $this->assertStringContainsString('IamShadowCompareService', $organizationController);
        $this->assertStringContainsString('IamShadowCompareService', $userManagementService);
        $this->assertStringContainsString('IamShadowCompareService', $authorizationService);
        $this->assertStringContainsString('compareCurrentUserPayload($user, $payload)', $userManagementService);
        $this->assertStringContainsString('compareRolesByUserId($userId, $roles', $authorizationService);
        $this->assertStringContainsString('comparePluginVerifyToken(', $pluginController);
        $this->assertStringContainsString('comparePermission($user, $permission, (bool)$allowed)', $organizationController);
        $this->assertStringContainsString('revokeUserSessions($userId)', $passwordService);
    }

    public function testCurrentBackendDoesNotDependOnStandaloneIdentityService(): void
    {
        $files = [
            'api/modules/v1/services/IdentityService.php',
            'api/modules/v1/services/SessionService.php',
            'api/modules/v1/services/AuthorizationService.php',
            'api/modules/v1/controllers/AuthController.php',
            '../files/api/config/main.php',
        ];

        $combined = '';
        foreach ($files as $relativePath) {
            $combined .= "\n" . $this->read($relativePath);
        }

        foreach ([
            'Keycloak',
            'IDENTITY_SERVICE_URL',
            'services/identity-service',
            '/api-auth',
            '.well-known/openid-configuration',
            'JWKS',
        ] as $needle) {
            $this->assertStringNotContainsString($needle, $combined);
        }
    }

    public function testIdentityProviderProxyIsGuardedAndLegacyByDefault(): void
    {
        $identityService = $this->read('api/modules/v1/services/IdentityService.php');
        $identityProviderClient = $this->read('api/modules/v1/services/IdentityProviderClient.php');
        $params = $this->read('../files/common/config/params.php');

        foreach ([
            'AUTH_PROVIDER',
            "return \$provider === 'identity' ? 'identity' : 'legacy';",
            'identityProviderClient()->login',
            'identityProviderClient()->refresh',
            'identityProviderClient()->logout',
            'IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK',
            'legacyRefresh($refreshToken',
            'Identity refresh failed; trying legacy refresh fallback.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityService);
        }

        foreach ([
            'IDENTITY_AUTH_BASE_URL',
            'IDENTITY_AUTH_TIMEOUT_MS',
            'IDENTITY_AUTH_CONNECT_TIMEOUT_MS',
            '/v1/auth/login',
            '/v1/auth/refresh',
            '/v1/auth/logout',
            'CURLOPT_CONNECTTIMEOUT_MS',
            'CURLOPT_TIMEOUT_MS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityProviderClient);
        }

        foreach ([
            "'AUTH_PROVIDER' => getenv('AUTH_PROVIDER') ?: 'legacy'",
            "'IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK' => getenv('IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK') ?: 'true'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $params);
        }
    }

    public function testIamShadowCompareIsInternalGuardedAndLegacyByDefault(): void
    {
        $identityProviderClient = $this->read('api/modules/v1/services/IdentityProviderClient.php');
        $iamShadowCompare = $this->read('api/modules/v1/services/IamShadowCompareService.php');
        $params = $this->read('../files/common/config/params.php');

        foreach ([
            'iamUserView(int $legacyUserId)',
            'iamRolesView(int $legacyUserId)',
            'iamPermissionsView(int $legacyUserId)',
            'iamOrganizationsView(int $legacyUserId)',
            'iamPluginVerifyToken(string $token)',
            "'X-Identity-Internal-Token: '",
            'IDENTITY_IAM_INTERNAL_API_TOKEN',
            'IDENTITY_INTERNAL_API_TOKEN',
            '/internal/iam/users/',
            '/internal/iam/plugin/verify-token',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityProviderClient);
        }

        foreach ([
            'IDENTITY_IAM_PROVIDER',
            'IDENTITY_IAM_SHADOW_COMPARE',
            'IDENTITY_IAM_FALLBACK',
            "return in_array(\$provider, ['identity', 'identity-shadow'], true) ? \$provider : 'legacy';",
            "provider() === 'identity-shadow'",
            'identity.iamShadowCompare',
            "hash('sha256'",
            "hash_hmac('sha256'",
            'legacyHash',
            'identityHash',
            'comparison.completed',
            'comparison.incomplete',
            'subjectHash',
            'fallbackEnabled()',
        ] as $needle) {
            $this->assertStringContainsString($needle, $iamShadowCompare);
        }

        $this->assertStringNotContainsString("'legacyUserId' => (int)\$user->id", $iamShadowCompare);

        foreach ([
            "'IDENTITY_IAM_PROVIDER' => getenv('IDENTITY_IAM_PROVIDER') ?: 'legacy'",
            "'IDENTITY_IAM_SHADOW_COMPARE' => getenv('IDENTITY_IAM_SHADOW_COMPARE') ?: 'false'",
            "'IDENTITY_IAM_SHADOW_COMPARE_HASH_SALT' => getenv('IDENTITY_IAM_SHADOW_COMPARE_HASH_SALT') ?: null",
            "'IDENTITY_IAM_FALLBACK' => getenv('IDENTITY_IAM_FALLBACK') ?: 'true'",
            "'IDENTITY_IAM_INTERNAL_API_TOKEN' => getenv('IDENTITY_IAM_INTERNAL_API_TOKEN')",
        ] as $needle) {
            $this->assertStringContainsString($needle, $params);
        }
    }

    public function testLoginAuditIsOptionalAndBypassOnly(): void
    {
        $identityService = $this->read('api/modules/v1/services/IdentityService.php');
        $reporter = $this->read('api/modules/v1/services/LoginAuditReporter.php');

        foreach ([
            'LoginAuditReporter',
            'reportSuccessfulLogin($user',
            '$token = $this->sessionService()->issueToken($user, $context);',
            'return $token;',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityService);
        }

        foreach ([
            'IDENTITY_LOGIN_AUDIT_ENABLED',
            'IDENTITY_LOGIN_AUDIT_URL',
            'IDENTITY_LOGIN_AUDIT_TOKEN',
            'CURLOPT_CONNECTTIMEOUT_MS',
            'CURLOPT_TIMEOUT_MS',
            'catch (\\Throwable $exception)',
            'return;',
            'identity.loginAudit',
        ] as $needle) {
            $this->assertStringContainsString($needle, $reporter);
        }

        foreach ([
            'password',
            'accessToken',
            'refreshToken',
            'Authorization',
        ] as $needle) {
            $this->assertStringNotContainsString("'{$needle}' =>", $reporter);
        }
    }

    public function testWechatRuntimeConfigIsAvailableInDockerImage(): void
    {
        $commonConfig = $this->read('../files/common/config/main-local.php');
        $apacheConfig = $this->read('../docker/000-default.conf');

        foreach ([
            "'wechat' => [",
            "'class' => \\common\\components\\WeChat::class",
            "'app_id' => getenv('WECHAT_APP_ID')",
            "getenv('WECHAT_APPID')",
            "'secret' => getenv('WECHAT_SECRET')",
            "'token' => getenv('WECHAT_TOKEN')",
        ] as $needle) {
            $this->assertStringContainsString($needle, $commonConfig);
        }

        foreach ([
            'PassEnv WECHAT_APP_ID WECHAT_APPID WECHAT_SECRET WECHAT_TOKEN',
            'PassEnv WECHAT_MCH_ID WECHAT_PAY_PRIVATE_KEY WECHAT_PAY_CERTIFICATE WECHAT_PAY_SECRET_KEY',
            'PassEnv WECHAT_PAY_NOTIFY_URL WECHAT_PAY_URL',
        ] as $needle) {
            $this->assertStringContainsString($needle, $apacheConfig);
        }
    }

    public function testAccountLifecycleProxyIsScopedGuardedAndLegacyByDefault(): void
    {
        $proxyService = $this->read('api/modules/v1/services/AccountLifecycleProxyService.php');
        $identityProviderClient = $this->read('api/modules/v1/services/IdentityProviderClient.php');
        $passwordController = $this->read('api/modules/v1/controllers/PasswordController.php');
        $emailController = $this->read('api/modules/v1/controllers/EmailController.php');
        $wechatController = $this->read('api/modules/v1/controllers/WechatController.php');
        $pluginUserController = $this->read('api/modules/v1/controllers/PluginUserController.php');
        $params = $this->read('../files/common/config/params.php');

        foreach ([
            'IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER',
            "return \$provider === 'identity' ? 'identity' : 'legacy';",
            'IDENTITY_ACCOUNT_LIFECYCLE_ENABLED',
            'IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK',
            'IDENTITY_ACCOUNT_REGISTER_ENABLED',
            'IDENTITY_ACCOUNT_PASSWORD_ENABLED',
            'IDENTITY_ACCOUNT_EMAIL_ENABLED',
            'IDENTITY_ACCOUNT_INVITATION_ENABLED',
            'X-Identity-Lifecycle-Proxy',
            'proxyCurrentRequest',
            'shouldFallbackFromProxyResult',
        ] as $needle) {
            $this->assertStringContainsString($needle, $proxyService);
        }

        foreach ([
            'proxyAccountLifecycle',
            'CURLOPT_CUSTOMREQUEST',
            'http_build_query($query)',
            'Authorization: ',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityProviderClient);
        }

        foreach ([
            "proxyCurrentRequest('password'",
            '/v1/password/request-reset',
            '/v1/password/verify-code',
            '/v1/password/reset',
            '/v1/password/change',
        ] as $needle) {
            $this->assertStringContainsString($needle, $passwordController);
        }

        foreach ([
            "proxyCurrentRequest('email'",
            '/v1/email/send-verification',
            '/v1/email/verify',
            '/v1/email/status',
            '/v1/email/send-change-confirmation',
            '/v1/email/verify-change-confirmation',
            '/v1/email/unbind',
            '/v1/email/cooldown',
        ] as $needle) {
            $this->assertStringContainsString($needle, $emailController);
        }

        foreach ([
            "proxyCurrentRequest('register'",
            '/v1/wechat/register',
            'use common\\models\\Wx;',
            'public function actionQrcode()',
            '$app = $wechat->application();',
            '$api = $app->getClient();',
            "/cgi-bin/qrcode/create",
            "showqrcode?ticket=",
            "'qrcode' => [",
            "'url' => \$url",
            "'ticket' => \$ticket",
            'public function actionRefresh()',
            "'message' => \$this->wechatUser(\$wechat) ? \"signin\" : \"signup\"",
            'private function findWechatRecord(string $token)',
            "Wechat::findOne(['token' => \$token])",
            "Wx::find()->where(['token' => \$token])->one()",
        ] as $needle) {
            $this->assertStringContainsString($needle, $wechatController);
        }

        foreach ([
            "proxyCurrentRequest('invitation'",
            '/v1/plugin-user/invitations',
            '/v1/plugin-user/create-invitation',
            '/v1/plugin-user/delete-invitation',
            '/v1/plugin-user/check-invitation',
            '/v1/plugin-user/invitation-records',
            '/v1/plugin-user/register-send-code',
            '/v1/plugin-user/register',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pluginUserController);
        }

        foreach ([
            "'IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER') ?: 'legacy'",
            "'IDENTITY_ACCOUNT_LIFECYCLE_ENABLED' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_ENABLED') ?: 'false'",
            "'IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK' => getenv('IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK') ?: 'true'",
            "'IDENTITY_ACCOUNT_REGISTER_ENABLED' => getenv('IDENTITY_ACCOUNT_REGISTER_ENABLED') ?: 'false'",
            "'IDENTITY_ACCOUNT_PASSWORD_ENABLED' => getenv('IDENTITY_ACCOUNT_PASSWORD_ENABLED') ?: 'false'",
            "'IDENTITY_ACCOUNT_EMAIL_ENABLED' => getenv('IDENTITY_ACCOUNT_EMAIL_ENABLED') ?: 'false'",
            "'IDENTITY_ACCOUNT_INVITATION_ENABLED' => getenv('IDENTITY_ACCOUNT_INVITATION_ENABLED') ?: 'false'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $params);
        }
    }
}
