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
        $passwordService = $this->read('api/modules/v1/services/PasswordResetService.php');

        $this->assertStringContainsString('PasswordAccountService', $passwordController);
        $this->assertStringContainsString('EmailAccountService', $emailController);
        $this->assertStringContainsString('UserManagementService', $userController);
        $this->assertStringContainsString('AuthorizationService', $pluginController);
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
}
