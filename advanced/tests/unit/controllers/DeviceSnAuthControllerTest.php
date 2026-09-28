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
use yii\web\HttpException;
use yii\web\TooManyRequestsHttpException;
use yii\web\User as WebUser;

final class DeviceSnAuthControllerTest extends TestCase
{
    private array $components = [];

    protected function setUp(): void
    {
        foreach (['request', 'response', 'user'] as $name) {
            $this->components[$name] = Yii::$app->get($name, false);
        }
        Yii::$app->set('request', new DeviceSnTestRequest());
        Yii::$app->set('response', new Response());
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

    #[DataProvider('supportedSnLengths')]
    public function testActivationAndLoginNormalizeCredentialsAndKeepTheExistingEnvelope(int $length): void
    {
        Yii::$app->request->setBodyParams(['sn' => implode('-', array_fill(0, intdiv($length, 4), 'aaaa')), 'uuid' => ' ROKID-device ']);
        $controller = $this->controller();
        $first = $controller->actionSnActivate();
        $second = $controller->actionSnLogin();

        $this->assertSame(['success' => true, 'message' => 'login', 'token' => RecordingSnLoginService::TOKEN], $first);
        $this->assertSame($first, $second);
        $this->assertSame([
            [str_repeat('A', $length), 'rokid-device', true],
            [str_repeat('A', $length), 'rokid-device', false],
        ], $controller->service->calls);
        $this->assertSame(['ip', 'sn', 'uuid', 'ip', 'sn', 'uuid'], array_column($controller->limiter->calls, 1));
        $this->assertSame(hash('sha256', str_repeat('A', $length)), $controller->limiter->calls[1][0]);
        $this->assertSame(hash('sha256', 'rokid-device'), $controller->limiter->calls[2][0]);
    }

    public static function supportedSnLengths(): array
    {
        return ['new' => [16], 'legacy' => [32]];
    }

    #[DataProvider('unsupportedSnLengths')]
    public function testOtherSnLengthsAreRejectedBeforeAuthentication(int $length, string $action): void
    {
        Yii::$app->request->setBodyParams(['sn' => str_repeat('A', $length), 'uuid' => 'rokid-device']);
        $controller = $this->controller();
        try {
            $controller->$action();
            self::fail('Unsupported SN length accepted.');
        } catch (HttpException $exception) {
            self::assertSame(400, $exception->statusCode);
            self::assertSame([], $controller->service->calls);
            self::assertSame(['ip'], array_column($controller->limiter->calls, 1));
        }
    }

    public static function unsupportedSnLengths(): iterable
    {
        foreach ([15, 17, 24, 31, 33] as $length) {
            foreach (['actionSnActivate', 'actionSnLogin'] as $action) {
                yield $action . '-' . $length => [$length, $action];
            }
        }
    }

    public function testRateLimitFailureDoesNotAuthenticateAndReturnsRetryAfter(): void
    {
        Yii::$app->request->setBodyParams(['sn' => str_repeat('A', 16), 'uuid' => 'rokid-device']);
        $controller = $this->controller();
        $controller->limiter->deny = 'uuid';
        try {
            $controller->actionSnLogin();
            $this->fail('Expected a rate limit.');
        } catch (TooManyRequestsHttpException) {
            $this->assertSame('37', Yii::$app->response->headers->get('Retry-After'));
            $this->assertSame([], $controller->service->calls);
        }
    }

    public function testRateLimitStorageFailureClosesTheDeviceLoginEndpoint(): void
    {
        $controller = $this->controller();
        $controller->limiter->fail = true;
        try {
            $controller->actionSnActivate();
            $this->fail('Expected unavailable limiter.');
        } catch (HttpException $exception) {
            $this->assertSame(503, $exception->statusCode);
            $this->assertSame([], $controller->service->calls);
        }
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
}

final class RecordingSnLoginService extends IdentityService
{
    public const TOKEN = ['accessToken' => 'access', 'expires' => '2030-01-01', 'refreshToken' => 'refresh'];
    public array $calls = [];
    public function loginDeviceSn(string $sn, string $uuid, bool $activate, array $context = []): array
    { $this->calls[] = [$sn, $uuid, $activate]; return self::TOKEN; }
}

final class DeviceSnTestLimiter
{
    public array $calls = [];
    public ?string $deny = null;
    public bool $fail = false;
    public function consume(string $identifier, string $strategy): array
    {
        if ($this->fail) { throw new \RuntimeException('storage failed'); }
        $this->calls[] = [$identifier, $strategy];
        return ['allowed' => $this->deny !== $strategy, 'retry_after' => 37];
    }
}

final class DeviceSnTestAuthController extends AuthController
{
    public RecordingSnLoginService $service;
    public DeviceSnTestLimiter $limiter;
    public function init()
    {
        parent::init();
        $this->service = new RecordingSnLoginService();
        $this->limiter = new DeviceSnTestLimiter();
    }
    protected function identityService(): IdentityService { return $this->service; }
    protected function requestContext(): array { return []; }
    protected function deviceRateLimiter() { return $this->limiter; }
}
