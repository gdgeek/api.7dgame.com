<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginController;
use api\modules\v1\controllers\PluginCampusController;
use api\modules\v1\controllers\PluginUserController;
use api\modules\v1\controllers\OrganizationController;
use bizley\jwt\JwtHttpBearerAuth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Request;
use yii\web\Response;
use yii\web\User as WebUser;

/**
 * Regression tests for malformed Bearer tokens on plugin-facing controllers.
 *
 * A malformed token such as "diagnostics-probe" should be treated as an
 * unauthorized request instead of surfacing a JWT parsing exception as HTTP 500.
 */
class PluginControllerAuthTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string}>
     */
    public static function pluginControllerProvider(): array
    {
        return [
            'plugin-controller' => [PluginController::class],
            'plugin-campus-controller' => [PluginCampusController::class],
            'plugin-user-controller' => [PluginUserController::class],
        ];
    }

    #[DataProvider('pluginControllerProvider')]
    public function testMalformedBearerTokenDoesNotThrowJwtStructureException(string $controllerClass): void
    {
        $controller = new $controllerClass('test', null);
        $behaviors = $controller->behaviors();

        $this->assertArrayHasKey('authenticator', $behaviors);
        $this->assertArrayHasKey('authMethods', $behaviors['authenticator']);
        $this->assertNotEmpty($behaviors['authenticator']['authMethods']);

        $authMethodConfig = $behaviors['authenticator']['authMethods'][0];
        if (is_string($authMethodConfig)) {
            $authMethodConfig = ['class' => $authMethodConfig];
        }

        /** @var JwtHttpBearerAuth $authMethod */
        $authMethod = Yii::createObject($authMethodConfig);

        $request = new Request();
        $request->getHeaders()->set('Authorization', 'Bearer diagnostics-probe');

        $user = new WebUser([
            'identityClass' => \api\modules\v1\models\User::class,
        ]);

        $result = $authMethod->authenticate($user, $request, new Response());

        $this->assertNull($result);
        $this->assertFalse(
            $authMethod->throwException,
            'Malformed plugin JWTs should fail as unauthorized instead of bubbling a parser exception.'
        );
    }

    public function testVerifyTokenRequiresJwtButBypassesRouteRbac(): void
    {
        $controller = new PluginController('test', null);
        $behaviors = $controller->behaviors();

        $this->assertArrayHasKey('authenticator', $behaviors);
        $this->assertArrayHasKey('access', $behaviors);

        $this->assertNotContains(
            'verify-token',
            $behaviors['authenticator']['except'] ?? [],
            'verify-token must still require a bearer token.'
        );
        $this->assertContains(
            'verify-token',
            $behaviors['access']['allowActions'] ?? [],
            'verify-token should not require an RBAC route assignment after JWT authentication.'
        );
    }

    public function testIamAuthorizationRouteOwnershipKeepsJwtAndIsDefaultOff(): void
    {
        $key = 'IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED';
        $previous = getenv($key);

        try {
            putenv($key . '=false');
            $pluginDefault = (new PluginUserController('test', null))->behaviors();
            $organizationDefault = (new OrganizationController('test', null))->behaviors();
            $this->assertSame([], $pluginDefault['access']['except']);
            $this->assertSame([], $organizationDefault['access']['except']);

            putenv($key . '=true');
            $pluginEnabled = (new PluginUserController('test', null))->behaviors();
            $organizationEnabled = (new OrganizationController('test', null))->behaviors();

            $this->assertSame([
                'users',
                'create-user',
                'batch-create-users',
                'update-user',
                'delete-user',
                'change-role',
                'invitations',
                'create-invitation',
                'delete-invitation',
                'invitation-records',
            ], $pluginEnabled['access']['except']);
            $this->assertSame(
                ['list', 'create', 'update', 'bind-user', 'unbind-user'],
                $organizationEnabled['access']['except']
            );

            $this->assertSame(
                $pluginDefault['authenticator']['except'],
                $pluginEnabled['authenticator']['except'],
                'Scoped route ownership must not widen JWT authentication exceptions.'
            );
            $this->assertSame(
                $organizationDefault['authenticator']['except'],
                $organizationEnabled['authenticator']['except'],
                'Organization route ownership must not widen JWT authentication exceptions.'
            );
        } finally {
            if ($previous === false) {
                putenv($key);
            } else {
                putenv($key . '=' . $previous);
            }
        }
    }
}
