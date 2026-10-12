<?php

namespace tests\unit\services;

use api\modules\v1\models\User;
use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityProviderClient;
use backend\controllers\IdentityAdminAssignmentController;
use backend\controllers\IdentityAdminUserController;
use common\components\IdentityBackendUser;
use common\components\IdentityDbManager;
use PHPUnit\Framework\TestCase;
use Yii;

/** Render original MDM/GridView HTML; disable only external JS/CSS asset publication. */
class IdentityLegacyAdminUiTest extends TestCase
{
    private $application;
    private $environment;
    private AdminUiClient $client;
    private $module;
    private array $configs;
    private string $runtimePath;

    protected function setUp(): void
    {
        if (!class_exists(\mdm\admin\Module::class)) $this->markTestSkipped('Installed locked MDM package is required.');
        Yii::setAlias('@mdm/admin', dirname((new \ReflectionClass(\mdm\admin\Module::class))->getFileName()));
        $this->application = Yii::$app;
        $this->environment = getenv('IDENTITY_DATA_SOURCE'); putenv('IDENTITY_DATA_SOURCE=identity');
        $this->client = new AdminUiClient();
        Yii::$container->set(IdentityProviderClient::class, fn() => $this->client);
        $this->runtimePath = sys_get_temp_dir() . '/identity-admin-ui-' . bin2hex(random_bytes(8));
        mkdir($this->runtimePath, 0700, true);
        new \yii\web\Application(['id' => 'admin-ui-test', 'basePath' => $this->application->basePath, 'runtimePath' => $this->runtimePath, 'viewPath' => $this->application->basePath . '/backend/views',
            'components' => ['request' => ['cookieValidationKey' => 'test-only-secret', 'hostInfo' => 'http://localhost', 'scriptUrl' => '', 'url' => '/admin/user'],
                'user' => ['class' => AdminUiUser::class, 'identityClass' => User::class, 'enableSession' => false],
                'authManager' => ['class' => IdentityDbManager::class],
                'identityAuthority' => ['class' => IdentityAuthorityService::class, 'client' => $this->client],
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'mysql:host=not-used;dbname=not-used'],
                'cache' => ['class' => \yii\caching\ArrayCache::class],
                'assetManager' => ['bundles' => ['yii\\web\\YiiAsset' => false, 'yii\\web\\JqueryAsset' => false,
                    'yii\\widgets\\ActiveFormAsset' => false, 'yii\\grid\\GridViewAsset' => false, 'mdm\\admin\\AnimateAsset' => false]],
            ]]);
        $this->module = new \mdm\admin\Module('admin', Yii::$app);
        $configs = \mdm\admin\components\Configs::instance();
        $this->configs = array_intersect_key(get_object_vars($configs), array_flip(['authManager', 'db', 'userDb', 'cache']));
        $configs->authManager = Yii::$app->authManager; $configs->db = Yii::$app->db; $configs->userDb = Yii::$app->db; $configs->cache = Yii::$app->cache;
    }
    protected function tearDown(): void
    {
        if (!isset($this->application)) return;
        putenv($this->environment === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $this->environment);
        foreach ($this->configs as $name => $value) \mdm\admin\components\Configs::instance()->$name = $value;
        Yii::$app->errorHandler->unregister();
        \yii\helpers\FileHelper::removeDirectory($this->runtimePath);
        Yii::$container->clear(IdentityProviderClient::class); Yii::$app = $this->application;
    }
    public function testOriginalUserListRendersPrivateRowsAndPagination(): void
    {
        $controller = $this->controller(IdentityAdminUserController::class, 'user');
        $html = $controller->actionIndex();
        $this->assertStringContainsString('user-index', $html);
        $this->assertStringContainsString('identity-user', $html);
        $this->assertStringContainsString('identity@example.test', $html);
        $this->assertSame('user-list', $this->client->calls[0]['operation']);
    }
    public function testOriginalUserDetailRendersSafePrivateFields(): void
    {
        $controller = $this->controller(IdentityAdminUserController::class, 'user');
        $html = $controller->actionView(7);
        $this->assertStringContainsString('user-view', $html);
        $this->assertStringContainsString('identity-user', $html);
        $this->assertStringNotContainsString('password_hash', $html);
    }
    public function testOriginalRoleListUsesCurrentIdentityGraph(): void
    {
        $controller = $this->controller(\backend\controllers\IdentityAdminRoleController::class, 'role');
        $html = $controller->actionIndex();
        $this->assertStringContainsString('role-index', $html);
        $this->assertStringContainsString('current-private-role', $html);
    }
    public function testOriginalAssignmentViewUsesIdentityUsernameAndAssignments(): void
    {
        $controller = $this->controller(IdentityAdminAssignmentController::class, 'assignment');
        $html = $controller->actionView(7);
        $this->assertStringContainsString('assignment-index', $html);
        $this->assertStringContainsString('identity-user', $html);
        $this->assertContains('rbac-snapshot', array_column($this->client->calls, 'operation'));
    }
    public function testOldDeleteAndActivateRoutesMutateOnlyIdentity(): void
    {
        $controller = $this->controller(IdentityAdminUserController::class, 'user');
        $controller->actionDelete(7); $controller->actionActivate(7);
        $this->assertSame(['user-delete', 'user-activate'], array_column($this->client->calls, 'operation'));
        $this->assertSame(['id' => 7], $this->client->calls[0]['payload']);
    }

    public function testOriginalMdmRoleCreatePostsThroughPrivateMutation(): void
    {
        $controller = $this->controller(\backend\controllers\IdentityAdminRoleController::class, 'role');
        Yii::$app->request->setBodyParams(['AuthItem' => ['name' => 'new-private-role', 'description' => 'Role description']]);
        $controller->actionCreate();
        $this->assertSame('rbac-mutate', $this->client->calls[0]['operation']);
        $this->assertSame('add-item', $this->client->calls[0]['payload']['action']);
        $this->assertSame('new-private-role', $this->client->calls[0]['payload']['item']['name']);
    }

    public function testOriginalRoleUsersReadsPrivateNamesAndKeepsPageLinks(): void
    {
        $controller = $this->controller(\backend\controllers\IdentityAdminRoleController::class, 'role');
        $result = $controller->actionGetUsers('current-private-role');
        $this->assertSame('identity-user', $result['users'][0]['username']);
        $this->assertStringContainsString('admin%2Fassignment%2Fview', $result['users'][0]['link']);
        $this->assertSame(0, $result['first']);
        $this->assertSame(['rbac-snapshot', 'user-view'], array_column($this->client->calls, 'operation'));
    }

    public function testRpcPermissionComesOnlyFromExecutingMdmRoute(): void
    {
        $controller = $this->controller(\backend\controllers\IdentityAdminRoleController::class, 'role');
        $controller->action = new \yii\base\InlineAction('create', $controller, 'actionCreate');
        $method = new \ReflectionMethod(IdentityProviderClient::class, 'legacyAdminEnvelope');
        $body = $method->invoke(new IdentityProviderClient(), 'rbac-mutate', ['permissionName' => '/admin/*', 'actorId' => 1], 'verified-access');
        $this->assertSame('/admin/role/create', $body['permissionName']);
        $this->assertSame('verified-access', $body['accessToken']);
        $controller = new \backend\controllers\HomeController('home', Yii::$app);
        $controller->action = new \yii\base\InlineAction('add-invitation', $controller, 'actionAddInvitation');
        Yii::$app->controller = $controller;
        $body = $method->invoke(new IdentityProviderClient(), 'invitation-create', ['permissionName' => '/admin/*'], 'verified-access');
        $this->assertArrayNotHasKey('permissionName', $body);
    }

    public function testIamAndAccountInternalScopesStayDistinct(): void
    {
        $names = ['IDENTITY_INTERNAL_API_TOKEN', 'IDENTITY_IAM_INTERNAL_API_TOKEN', 'IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN', 'IDENTITY_ACCOUNT_INTERNAL_TOKEN'];
        $old = array_combine($names, array_map('getenv', $names));
        try {
            putenv('IDENTITY_INTERNAL_API_TOKEN=general-test-key');
            putenv('IDENTITY_IAM_INTERNAL_API_TOKEN=iam-test-key');
            putenv('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN=auth-test-key');
            $client = new IdentityProviderClient();
            $this->assertSame('iam-test-key', (new \ReflectionMethod($client, 'internalIamToken'))->invoke($client));
            $this->assertSame('auth-test-key', (new \ReflectionMethod($client, 'internalAuthToken'))->invoke($client));
            putenv('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN='); putenv('IDENTITY_ACCOUNT_INTERNAL_TOKEN=account-test-key');
            $this->assertSame('account-test-key', (new \ReflectionMethod($client, 'internalAuthToken'))->invoke($client));
        } finally {
            foreach ($old as $name => $value) putenv($value === false ? $name : $name . '=' . $value);
        }
    }

    public function testOriginalHomeInvitationPageUsesPrivateRecipientsAndRoleChoices(): void
    {
        $controller = new \backend\controllers\HomeController('home', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $html = $controller->actionInvitation();
        $this->assertStringContainsString('invitation-index', $html);
        $this->assertStringContainsString('identity-invite-code', $html);
        $this->assertStringContainsString('private-recipient', $html);
        $controller->actionAddInvitation();
        $this->assertSame(['invitation-list', 'invitation-create'], array_column($this->client->calls, 'operation'));
    }

    public function testTrialInvitationKeepsBusinessInfoAndPrivateIdempotentCode(): void
    {
        Yii::$app->set('db', new \yii\db\Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->createCommand('CREATE TABLE info(id INTEGER PRIMARY KEY,name TEXT,company TEXT,tel TEXT,reason TEXT,invitation TEXT)')->execute();
        Yii::$app->db->createCommand()->insert('info', ['id' => 19, 'name' => 'name', 'company' => 'company', 'tel' => '13912345678', 'reason' => 'trial'])->execute();
        Yii::$app->request->setQueryParams(['tel' => '13912345678']);
        $controller = new \backend\controllers\InfoController('info', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $html = $controller->actionCreateInvitation();
        $this->assertStringContainsString('identity-trial-code', Yii::$app->view->blocks['content-main']);
        $this->assertSame('identity-trial-code', Yii::$app->db->createCommand('SELECT invitation FROM info WHERE id=19')->queryScalar());
        $this->assertSame('trial-create', $this->client->calls[0]['operation']);
        $this->assertSame(['businessInfoId' => 19], $this->client->calls[0]['payload']);
        $this->assertNull($this->client->calls[0]['accessToken']);
        $controller->actionCreateInvitation();
        $this->assertCount(1, $this->client->calls);
    }
    private function controller(string $class, string $id)
    {
        $controller = new $class($id, $this->module, ['layout' => false]);
        Yii::$app->controller = $controller;
        return $controller;
    }
}
class AdminUiMemoryUser extends User
{
    public function attributes() { return ['id', 'username', 'status']; }
}
class AdminUiUser extends IdentityBackendUser
{
    public function identityAccessToken(): ?string { return 'current-admin-access'; }
    public function getIdentity($autoRenew = true) { $user = new AdminUiMemoryUser(); $user->id = 1; $user->username = 'root'; $user->status = 10; return $user; }
}
class AdminUiClient extends IdentityProviderClient
{
    public array $calls = [];
    public function identityPermission(int $id, string $permission): bool { return true; }
    public function identityPolicyGraph(): array
    {
        return ['items' => [['name' => 'current-private-role', 'type' => 1]], 'children' => [], 'rules' => []];
    }
    public function legacyAdmin(string $operation, array $payload = [], ?string $accessToken = null): array
    {
        $this->calls[] = compact('operation', 'payload', 'accessToken');
        $user = ['id' => 7, 'username' => 'identity-user', 'nickname' => null, 'email' => 'identity@example.test', 'status' => 10, 'created_at' => 1700000000, 'updated_at' => 1700000000];
        if ($operation === 'user-list') return ['users' => [$user], 'total' => 1, 'page' => 1, 'pageSize' => 20];
        if ($operation === 'user-view') return $user;
        if ($operation === 'rbac-snapshot') return ['assignments' => [['user_id' => '7', 'item_name' => 'current-private-role', 'created_at' => 1700000000]]];
        if ($operation === 'invitation-list') return ['invitations' => [['id' => 8, 'code' => 'identity-invite-code', 'sender_id' => 1,
            'recipient_id' => 7, 'recipient_username' => 'private-recipient', 'used' => 0, 'auth_item_name' => 'user', 'create_at' => '2026-10-12 00:00:00']], 'roles' => [['name' => 'user']]];
        if ($operation === 'trial-create') return ['code' => 'identity-trial-code'];
        return ['success' => true];
    }
}
