<?php

namespace tests\unit\controllers;

use api\modules\v1\components\DeviceSnAuthGuard;
use api\modules\v1\controllers\AuthController;
use api\modules\v1\controllers\EmailController;
use api\modules\v1\controllers\PasswordController;
use api\modules\v1\controllers\PluginCampusController;
use api\modules\v1\controllers\PluginUserController;
use api\modules\v1\controllers\ToolsController;
use api\modules\v1\models\User;
use api\modules\v1\services\IdentityService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\GoneHttpException;
use yii\web\UrlManager;
use yii\web\User as WebUser;

final class DeviceSnAuthControllerTest extends TestCase
{
    private array $components = [];

    protected function setUp(): void
    {
        foreach (['request', 'response', 'user', 'errorHandler'] as $name) {
            $this->components[$name] = Yii::$app->get($name, false);
        }
        Yii::$app->set('request', new DeviceSnTestRequest());
        Yii::$app->set('response', new Response());
        Yii::$app->set('errorHandler', new \yii\web\ErrorHandler());
        $webUser = new WebUser(['identityClass' => User::class, 'enableSession' => false]);
        $user = new DeviceSnGuardTestUser();
        $user->id = 42;
        $webUser->setIdentity($user);
        Yii::$app->set('user', $webUser);
    }

    protected function tearDown(): void
    {
        foreach ($this->components as $name => $component) {
            Yii::$app->set($name, $component);
        }
    }

    #[DataProvider('retiredEndpoints')]
    public function testRetiredSnEndpointsReturnGoneWithoutReadingCredentialsOrCallingAnIssuer(string $action, array $body): void
    {
        Yii::$app->user->setIdentity(null);
        Yii::$app->request->setBodyParams($body);
        $controller = $this->controller();
        try {
            $controller->runAction($action);
            self::fail('Retired SN endpoint accepted a request.');
        } catch (GoneHttpException $exception) {
            self::assertSame(410, $exception->statusCode);
            self::assertStringContainsString('y1', $exception->getMessage());
            self::assertStringContainsString('/v1/auth/' . $action, $exception->getMessage());
            self::assertSame([], $controller->service->calls);
        }
    }

    public static function retiredEndpoints(): iterable
    {
        foreach (['sn-activate', 'sn-login'] as $action) {
            foreach ([
                'sixteen' => ['sn' => str_repeat('A', 16), 'uuid' => 'rokid-device'],
                'historical' => ['sn' => str_repeat('A', 32), 'uuid' => 'rokid-device'],
                'malformed' => ['sn' => ['not-a-string'], 'uuid' => null],
                'empty' => [],
            ] as $name => $body) {
                yield $action . '-' . $name => [$action, $body];
            }
        }
    }

    public function testDefaultYiiRouteAlsoReachesOnlyTheRetiredAction(): void
    {
        Yii::$app->user->setIdentity(null);
        $manager = new UrlManager(['enablePrettyUrl' => false]);
        foreach (['sn-activate', 'sn-login'] as $action) {
            Yii::$app->request->setQueryParams(['r' => 'v1/auth/' . $action]);
            [$route] = $manager->parseRequest(Yii::$app->request);
            self::assertSame('v1/auth/' . $action, $route);
            $controller = $this->controller();
            try {
                $controller->runAction(substr($route, strlen('v1/auth/')));
                self::fail('Default routing restored the retired issuer.');
            } catch (GoneHttpException $exception) {
                self::assertSame(410, $exception->statusCode);
                self::assertSame([], $controller->service->calls);
            }
        }
    }

    public function testExistingPasswordRefreshAndLogoutStillUseTheOriginalService(): void
    {
        $controller = $this->controller();
        Yii::$app->request->setBodyParams(['username' => 'ordinary', 'password' => 'fixture-only']);
        self::assertSame(RecordingSnLoginService::TOKEN, $controller->actionLogin()['token']);
        Yii::$app->request->setBodyParams(['refreshToken' => 'existing-session']);
        self::assertSame(RecordingSnLoginService::TOKEN, $controller->actionRefresh()['token']);
        self::assertTrue($controller->actionLogout()['revoked']);
        self::assertSame([
            ['login', 'ordinary', 'fixture-only'],
            ['refresh', 'existing-session'],
            ['logout', 'existing-session'],
        ], $controller->service->calls);
    }

    #[DataProvider('credentialActions')]
    public function testDeviceSessionCannotDeriveCredentials(string $class, string $action, array $body = []): void
    {
        Yii::$app->user->identity->authContext = ['auth_method' => 'device_sn', 'device_sn_id' => 9];
        Yii::$app->request->setBodyParams($body);
        $controller = new $class('test', Yii::$app);
        $this->expectException(ForbiddenHttpException::class);
        // No database, mail or provider is installed: rejection must occur
        // before any of those credential-changing side effects are reached.
        $controller->$action();
    }

    public static function credentialActions(): array
    {
        return [
            [ToolsController::class, 'actionUserLinked'],
            [EmailController::class, 'actionSendVerification'],
            [EmailController::class, 'actionVerify'],
            [EmailController::class, 'actionSendChangeConfirmation'],
            [EmailController::class, 'actionVerifyChangeConfirmation'],
            [EmailController::class, 'actionUnbind'],
            [PasswordController::class, 'actionChange'],
            [PluginCampusController::class, 'actionPassword'],
            [PluginUserController::class, 'actionUpdateUser', ['password' => 'new-password']],
            [PluginUserController::class, 'actionUpdateUser', ['email' => 'new@example.com']],
        ];
    }

    public function testGuardUsesVerifiedIdentityRatherThanClientBody(): void
    {
        Yii::$app->request->setBodyParams(['auth_method' => 'device_sn', 'device_sn_id' => 9]);
        DeviceSnAuthGuard::assertCredentialManagementAllowed();
        $this->assertSame([], Yii::$app->user->identity->authContext);
    }

    private function controller(): DeviceSnTestAuthController
    {
        return new DeviceSnTestAuthController('auth', Yii::$app);
    }
}

final class DeviceSnGuardTestUser extends User
{
    public function attributes(): array { return ['id']; }
}

final class DeviceSnTestRequest extends Request
{
    public function getUserIP(): string { return '203.0.113.9'; }
    public function getMethod() { return 'POST'; }
}

final class RecordingSnLoginService extends IdentityService
{
    public const TOKEN = ['accessToken' => 'access', 'expires' => '2030-01-01', 'refreshToken' => 'refresh'];
    public array $calls = [];
    public function loginDeviceSn(string $sn, string $uuid, bool $activate, array $context = []): array
    { $this->calls[] = ['sn', $sn, $uuid, $activate]; return self::TOKEN; }
    public function login($username, $password, array $context = []): array
    { $this->calls[] = ['login', $username, $password]; return self::TOKEN; }
    public function refresh($refreshToken, array $context = []): array
    { $this->calls[] = ['refresh', $refreshToken]; return self::TOKEN; }
    public function logout(?string $refreshToken): bool
    { $this->calls[] = ['logout', $refreshToken]; return true; }
}

final class DeviceSnTestAuthController extends AuthController
{
    public RecordingSnLoginService $service;
    public function init()
    {
        parent::init();
        $this->service = new RecordingSnLoginService();
    }
    protected function identityService(): IdentityService { return $this->service; }
    protected function requestContext(): array { return []; }
}
