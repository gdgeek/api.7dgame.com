<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginSnController;
use api\modules\v1\models\User;
use api\modules\v1\services\PluginAccessService;
use api\modules\v1\services\PluginAccessConfigClient;
use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\ActionEvent;
use yii\db\Connection;
use yii\rbac\DbManager;
use yii\web\HttpException;
use yii\web\Response;

final class DeviceSnManagementAccessTest extends TestCase
{
    private array $original = [];
    private Connection $db;
    private PluginSnController $controller;
    private PluginAccessConfigClient $configClient;

    protected function setUp(): void
    {
        foreach (['db', 'pluginAccessConfigClient', 'authManager', 'user', 'response', 'errorHandler'] as $id) {
            $this->original[$id] = Yii::$app->get($id, false);
        }
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $this->db);
        $this->configClient = new class(['db' => $this->db]) extends PluginAccessConfigClient {
            public Connection $db;
            public function read(string $pluginId): ?array
            {
                $row = (new \yii\db\Query())->from('plugins')->where(['id' => $pluginId])->one($this->db);
                return $row ? ['enabled' => (bool)$row['enabled'], 'access_scope' => $row['access_scope']] : null;
            }
        };
        Yii::$app->set('pluginAccessConfigClient', $this->configClient);
        Yii::$app->set('authManager', new DbManager(['db' => $this->db]));
        Yii::$app->set('response', new Response());
        Yii::$app->set('errorHandler', new \yii\web\ErrorHandler());
        Yii::$app->set('user', new \yii\web\User(['identityClass' => User::class, 'enableSession' => false]));
        foreach ([
            'CREATE TABLE user (id INTEGER PRIMARY KEY, username TEXT, status INTEGER)',
            'CREATE TABLE auth_item (name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER)',
            'CREATE TABLE auth_assignment (item_name TEXT, user_id TEXT, created_at INTEGER)',
            'CREATE TABLE auth_item_child (parent TEXT, child TEXT)',
            'CREATE TABLE plugins (id TEXT PRIMARY KEY, enabled INTEGER, access_scope TEXT)',
        ] as $sql) {
            $this->db->createCommand($sql)->execute();
        }
        foreach (['root', 'admin', 'manager', 'user'] as $index => $role) {
            $id = $index + 1;
            $this->db->createCommand()->insert('user', ['id' => $id, 'username' => $role, 'status' => 10])->execute();
            $this->db->createCommand()->insert('auth_item', ['name' => $role, 'type' => 1])->execute();
            $this->db->createCommand()->insert('auth_assignment', ['item_name' => $role, 'user_id' => (string)$id])->execute();
        }
        $this->db->createCommand()->insert('plugins', ['id' => 'sn-management', 'enabled' => 1, 'access_scope' => 'root-only'])->execute();
        $this->controller = new PluginSnController('plugin-sn', Yii::$app);
    }

    protected function tearDown(): void
    {
        foreach ($this->original as $id => $component) {
            Yii::$app->set($id, $component);
        }
        $this->db->close();
    }

    private function identity(int $id): User
    {
        $user = User::findOne($id);
        Yii::$app->user->setIdentity($user);
        return $user;
    }

    private function configure(?string $scope, int $enabled = 1): void
    {
        $this->db->createCommand()->update('plugins', ['access_scope' => $scope, 'enabled' => $enabled], ['id' => 'sn-management'])->execute();
    }

    private function assertHttp(int $status, callable $action): void
    {
        try {
            $action();
            self::fail('Expected HTTP ' . $status);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->statusCode);
            if ($status === 503) {
                self::assertInstanceOf(PluginAccessConfigUnavailableException::class, $exception);
            }
        }
    }

    public function testAccessRequiresAuthenticationAndRevealsOnlyCapability(): void
    {
        $this->assertHttp(401, fn() => $this->controller->actionAccess());
        $this->identity(1);
        self::assertSame(['success' => true, 'data' => ['allowed' => true, 'access_scope' => 'root-only']],
            $this->controller->actionAccess());
        self::assertSame('no-store', Yii::$app->response->headers->get('Cache-Control'));
        self::assertSame('no-cache', Yii::$app->response->headers->get('Pragma'));
    }

    public function testScopeChangesRevokeTheExistingAdminIdentityWithoutReloginOrCaching(): void
    {
        $identity = $this->identity(2);
        $service = new PluginAccessService($this->configClient);
        self::assertFalse($service->resolve('sn-management', $identity)['allowed']);
        $this->assertHttp(403, fn() => $this->controller->actionGenerate());
        $this->configure('admin-only');
        self::assertTrue($service->resolve('sn-management', $identity)['allowed']);
        self::assertTrue($this->controller->actionAccess()['data']['allowed']);
        // No legacy SN route permission has been assigned to admin. The exact
        // dynamic actions must still reach their authenticated configuration guard.
        $action = $this->controller->createAction('generate');
        $event = new ActionEvent($action);
        $this->controller->getBehavior('access')->beforeFilter($event);
        self::assertTrue($event->isValid);
        $this->configure('root-only');
        self::assertFalse($service->resolve('sn-management', $identity)['allowed']);
        self::assertSame($identity, Yii::$app->user->identity);
        self::assertFalse($this->controller->actionAccess()['data']['allowed']);
        $this->assertHttp(403, fn() => $this->controller->actionGenerate());
    }

    #[DataProvider('scopeMatrix')]
    public function testConfiguredScopeUsesCurrentRoles(?string $scope, array $expected): void
    {
        $this->configure($scope);
        foreach ($expected as $id => $allowed) {
            $this->identity($id);
            self::assertSame($allowed, $this->controller->actionAccess()['data']['allowed']);
        }
    }

    public static function scopeMatrix(): array
    {
        return [
            ['root-only', [1 => true, 2 => false, 3 => false, 4 => false]],
            ['admin-only', [1 => true, 2 => true, 3 => false, 4 => false]],
            ['manager-only', [1 => true, 2 => true, 3 => true, 4 => false]],
            ['auth-only', [1 => true, 2 => true, 3 => true, 4 => true]],
        ];
    }

    public function testDisabledMissingOrUnavailableConfigurationNeverAllowsRoot(): void
    {
        $this->identity(1);
        $this->configure('root-only', 0);
        self::assertSame(['allowed' => false, 'access_scope' => 'root-only'], $this->controller->actionAccess()['data']);
        $this->assertHttp(403, fn() => $this->controller->actionExport());
        $this->db->createCommand()->delete('plugins', ['id' => 'sn-management'])->execute();
        self::assertSame(['allowed' => false, 'access_scope' => null], $this->controller->actionAccess()['data']);
        $this->assertHttp(403, fn() => $this->controller->actionReveal(1));
        $this->db->createCommand('DROP TABLE plugins')->execute();
        try {
            $this->controller->actionAccess();
            self::fail('Missing authority must fail closed.');
        } catch (HttpException $exception) {
            self::assertInstanceOf(PluginAccessConfigUnavailableException::class, $exception);
            self::assertSame(503, $exception->statusCode);
            self::assertSame('Plugin access configuration is unavailable.', $exception->getMessage());
            self::assertNull($exception->getPrevious());
        }
        $this->assertHttp(503, fn() => $this->controller->actionIndex());
    }

    public function testMissingAndInvalidScopeNeverBecomeAuthOnlyOrRootFallback(): void
    {
        $this->identity(1);
        foreach ([null, '', 'invalid'] as $scope) {
            $this->configure($scope);
            $this->assertHttp(503, fn() => $this->controller->actionAccess());
            $this->assertHttp(503, fn() => $this->controller->actionGenerate());
        }
    }

    public function testEveryManagementActionRejectsDeviceCredentialsEvenAtAuthOnly(): void
    {
        $this->configure('auth-only');
        foreach ([1, 4] as $id) {
            $user = $this->identity($id);
            $user->authContext = ['auth_method' => 'device_sn', 'device_sn_id' => 42];
            self::assertSame(['allowed' => false, 'access_scope' => null], $this->controller->actionAccess()['data']);
            foreach ([
                fn() => $this->controller->actionIndex(), fn() => $this->controller->actionAccounts(),
                fn() => $this->controller->actionView(1), fn() => $this->controller->actionGenerate(),
                fn() => $this->controller->actionUpdate(1), fn() => $this->controller->actionReveal(1),
                fn() => $this->controller->actionExport(),
            ] as $action) {
                $this->assertHttp(403, $action);
            }
        }
    }

    public function testInactiveAccountAndLiveRoleRevocationCannotKeepManagementAccess(): void
    {
        $identity = $this->identity(2);
        $this->configure('admin-only');
        self::assertTrue($this->controller->actionAccess()['data']['allowed']);
        $this->db->createCommand()->delete('auth_assignment', ['user_id' => '2'])->execute();
        self::assertFalse($this->controller->actionAccess()['data']['allowed']);
        $identity->status = 0;
        $this->assertHttp(401, fn() => $this->controller->actionAccess());
    }
}
