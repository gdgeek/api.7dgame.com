<?php

namespace tests\unit\contracts;

use common\components\security\LogFilter;
use PHPUnit\Framework\TestCase;

final class IdentitySurfaceContractTest extends TestCase
{
    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3) . '/' . $relativePath;
        $this->assertFileExists($path);

        return (string)file_get_contents($path);
    }

    public function testIdentityRoutesRemainRegistered(): void
    {
        $config = $this->read('../files/api/config/main.php');

        foreach ([
            "'controller' => 'v1/auth'",
            "'POST login' => 'login'",
            "'POST refresh' => 'refresh'",
            "'POST logout' => 'logout'",
            "'DELETE logout' => 'logout'",
            "'controller' => 'v1/user'",
            "'GET info' => 'info'",
            "'PUT update' => 'update'",
            "'controller' => 'v1/password'",
            "'POST request-reset' => 'request-reset'",
            "'POST verify-code' => 'verify-code'",
            "'POST reset' => 'reset'",
            "'POST change' => 'change'",
            "'controller' => 'v1/email'",
            "'POST send-verification' => 'send-verification'",
            "'POST verify' => 'verify'",
            "'POST unbind' => 'unbind'",
            "'controller' => 'v1/wechat'",
            "'POST login' => 'login'",
            "'POST register' => 'register'",
            "'controller' => 'v1/plugin'",
            "'GET verify-token' => 'verify-token'",
            "'controller' => 'v1/plugin-user'",
            "'GET me' => 'me'",
            "'GET users' => 'users'",
            "'POST create-user' => 'create-user'",
            "'POST update-user' => 'update-user'",
            "'POST delete-user' => 'delete-user'",
            "'POST change-role' => 'change-role'",
            "'POST register-send-code' => 'register-send-code'",
            "'POST batch-create-users' => 'batch-create-users'",
            "'controller' => 'v1/organization'",
            "'GET list' => 'list'",
            "'POST bind-user' => 'bind-user'",
            "'POST unbind-user' => 'unbind-user'",
            "'controller' => 'v1/person'",
            "'PUT auth' => 'auth'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $config);
        }
    }

    public function testLoginAndRefreshContractsRemainCompatible(): void
    {
        $controller = $this->read('api/modules/v1/controllers/AuthController.php');
        $identityService = $this->read('api/modules/v1/services/IdentityService.php');

        foreach ([
            'public function actionLogin()',
            'IdentityService',
            '->login($username, $password',
            "'success' => true",
            "'message' => \"login\"",
            'public function actionRefresh()',
            '->refresh($refreshToken',
            "'message' => \"refresh\"",
            'public function actionLogout()',
            "'message' => 'logout'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $controller);
        }

        foreach ([
            'username is required',
            'password is required',
            'User::findByUsername($username)',
            'no user',
            'wrong password',
            'reportSuccessfulLogin($user',
            'refreshToken is required',
            'consumeRefreshToken($refreshToken)',
            'IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK',
            'save error',
        ] as $needle) {
            $this->assertStringContainsString($needle, $identityService);
        }
    }

    public function testCurrentUserAndPluginVerificationContractsRemainCompatible(): void
    {
        $userController = $this->read('api/modules/v1/controllers/UserController.php');
        $userManagementService = $this->read('api/modules/v1/services/UserManagementService.php');
        $pluginController = $this->read('api/modules/v1/controllers/PluginController.php');

        foreach ([
            'UserManagementService',
            'buildCurrentUserPayload($user)',
            "public function actionInfo()",
            "'success' => true",
            "'message'=>'ok'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $userController);
        }

        foreach ([
            "'id' => \$user->id",
            "'userData' => \$user->data",
            "'userInfo' => \$user->userInfo",
            "'roles' => \$user->roles",
            "'organizations' => User::normalizeOrganizations",
            "'emailBind' => \$emailBind",
        ] as $needle) {
            $this->assertStringContainsString($needle, $userManagementService);
        }

        foreach ([
            'public function actionVerifyToken()',
            "'code' => 0",
            "'message' => 'ok'",
            "'id' => \$user->id",
            "'username' => \$user->username",
            "'roles' => \$roles",
            "'organizations' => User::normalizeOrganizations",
            "'verify-token' => ['GET']",
        ] as $needle) {
            $this->assertStringContainsString($needle, $pluginController);
        }
    }

    public function testPasswordResetAndRefreshTokenRevocationContractsRemainCompatible(): void
    {
        $userModel = $this->read('api/modules/v1/models/User.php');
        $sessionService = $this->read('api/modules/v1/services/SessionService.php');
        $passwordService = $this->read('api/modules/v1/services/PasswordResetService.php');

        foreach ([
            'public static function findByRefreshToken($refreshToken)',
            'SessionService',
            'consumeRefreshToken($refreshToken)',
            'public function token()',
            'issueToken($this)',
        ] as $needle) {
            $this->assertStringContainsString($needle, $userModel);
        }

        foreach ([
            'RefreshToken::hashToken($refreshToken)',
            '$token->delete();',
            '$token->key = RefreshToken::hashToken($refreshToken);',
            "'accessToken'",
            "'refreshToken'",
            "'expires'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $sessionService);
        }

        foreach ([
            'revokeUserSessions($userId)',
            'Password reset successful',
            'Password changed successfully',
            'Invalidated {$deletedCount} sessions',
            'maskTokenForLog',
        ] as $needle) {
            $this->assertStringContainsString($needle, $passwordService);
        }
    }

    public function testSensitiveIdentityValuesAreMaskedInLogs(): void
    {
        $message = 'password=secret refresh_token=abc Authorization=Bearer eyJabc.eyJdef.sig token="abc"';
        $filtered = LogFilter::filter($message);

        $this->assertStringNotContainsString('secret', $filtered);
        $this->assertStringNotContainsString('refresh_token=abc', $filtered);
        $this->assertStringNotContainsString('Bearer eyJabc.eyJdef.sig', $filtered);
        $this->assertStringContainsString('[FILTERED]', $filtered);
    }
}
