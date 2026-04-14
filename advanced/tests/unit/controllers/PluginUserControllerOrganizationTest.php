<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginUserController;
use api\modules\v1\models\Organization;
use api\modules\v1\models\User;
use api\modules\v1\models\UserInfo;
use api\modules\v1\models\UserOrganization;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\web\Response;
use yii\web\User as WebUser;

final class PluginUserControllerOrganizationTest extends TestCase
{
    private const OPERATOR_USERNAME = 'plugin-user-org-operator';
    private const DETAIL_TARGET_USERNAME = 'plugin-user-org-detail-target';
    private const UPDATE_TARGET_USERNAME = 'plugin-user-org-update-target';
    private const CREATE_TARGET_USERNAME = 'plugin-user-org-create-target';
    private const ORGANIZATION_NAMES = [
        'plugin-user-org-acme',
        'plugin-user-org-global',
        'plugin-user-org-north',
    ];

    private $originalUserComponent;
    private $originalAuthManager;
    private $originalResponseComponent;
    private $originalRequestComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalUserComponent = Yii::$app->get('user', false);
        $this->originalAuthManager = Yii::$app->get('authManager', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->originalRequestComponent = Yii::$app->get('request', false);
        $this->cleanupFixtures();
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->set('authManager', $this->originalAuthManager);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->set('request', $this->originalRequestComponent);
        $this->cleanupFixtures();
        parent::tearDown();
    }

    public function testUsersDetailIncludesOrganizations(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'operator@example.com');
        $target = $this->createUser(self::DETAIL_TARGET_USERNAME, 'detail@example.com');
        $organization = $this->createOrganization('North Team', self::ORGANIZATION_NAMES[2]);

        $this->bindOrganization($target->id, $organization->id);
        $this->bootAuthenticatedOperator($operator->id, ['view-user']);
        Yii::$app->set('request', new PluginUserTestRequest(['id' => (string) $target->id], []));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $result = $controller->actionUsers();

        $this->assertSame(0, $result['code']);
        $this->assertArrayHasKey('organizations', $result['data']);
        $this->assertSame([
            ['id' => $organization->id, 'name' => self::ORGANIZATION_NAMES[2], 'title' => 'North Team'],
        ], $result['data']['organizations']);
    }

    public function testCreateUserPersistsOrganizationBindingsAndReturnsOrganizations(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'operator@example.com');
        $organizationA = $this->createOrganization('Acme Studio', self::ORGANIZATION_NAMES[0]);
        $organizationB = $this->createOrganization('Global Team', self::ORGANIZATION_NAMES[1]);

        $this->bootAuthenticatedOperator($operator->id, ['create-user']);
        Yii::$app->set('request', new PluginUserTestRequest([], [
            'username' => self::CREATE_TARGET_USERNAME,
            'nickname' => 'Created Nickname',
            'password' => 'secret123',
            'email' => 'created@example.com',
            'status' => 10,
            'organization_ids' => [$organizationA->id, $organizationB->id],
        ]));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $result = $controller->actionCreateUser();

        $createdUser = User::findOne(['username' => self::CREATE_TARGET_USERNAME]);
        $bindings = UserOrganization::find()
            ->where(['user_id' => $createdUser->id])
            ->orderBy(['organization_id' => SORT_ASC])
            ->all();

        $this->assertSame(0, $result['code']);
        $this->assertSame('Created Nickname', $createdUser->nickname);
        $this->assertSame('Created Nickname', $result['data']['nickname']);
        $this->assertCount(2, $bindings);
        $this->assertSame(
            [$organizationA->id, $organizationB->id],
            array_map(static fn (UserOrganization $binding): int => (int) $binding->organization_id, $bindings)
        );
        $this->assertSame([
            ['id' => $organizationA->id, 'name' => self::ORGANIZATION_NAMES[0], 'title' => 'Acme Studio'],
            ['id' => $organizationB->id, 'name' => self::ORGANIZATION_NAMES[1], 'title' => 'Global Team'],
        ], $result['data']['organizations']);
    }

    public function testCreateUserRejectsUnknownOrganizationIds(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'operator@example.com');

        $this->bootAuthenticatedOperator($operator->id, ['create-user']);
        Yii::$app->set('request', new PluginUserTestRequest([], [
            'username' => self::CREATE_TARGET_USERNAME,
            'password' => 'secret123',
            'email' => 'created@example.com',
            'status' => 10,
            'organization_ids' => [999999],
        ]));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $result = $controller->actionCreateUser();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertSame(4220, $result['code']);
        $this->assertNull(User::findOne(['username' => self::CREATE_TARGET_USERNAME]));
    }

    public function testUpdateUserCanReplaceOrganizationsAndUpdateNicknameWithoutRenamingUsername(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'operator@example.com');
        $target = $this->createUser(self::UPDATE_TARGET_USERNAME, 'update@example.com');
        $organizationA = $this->createOrganization('Acme Studio', self::ORGANIZATION_NAMES[0]);
        $organizationB = $this->createOrganization('Global Team', self::ORGANIZATION_NAMES[1]);

        $this->bindOrganization($target->id, $organizationA->id);

        $this->bootAuthenticatedOperator($operator->id, ['update-user']);
        Yii::$app->set('request', new PluginUserTestRequest([], [
            'id' => $target->id,
            'username' => 'renamed-attempt',
            'nickname' => 'Updated Nickname',
            'organization_ids' => [$organizationB->id],
        ]));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $result = $controller->actionUpdateUser();
        $target->refresh();

        $bindings = UserOrganization::find()
            ->where(['user_id' => $target->id])
            ->orderBy(['organization_id' => SORT_ASC])
            ->all();

        $this->assertSame(0, $result['code']);
        $this->assertSame(self::UPDATE_TARGET_USERNAME, $target->username);
        $this->assertSame('Updated Nickname', $target->nickname);
        $this->assertSame('Updated Nickname', $result['data']['nickname']);
        $this->assertSame([$organizationB->id], array_map(
            static fn (UserOrganization $binding): int => (int) $binding->organization_id,
            $bindings
        ));
        $this->assertSame([
            ['id' => $organizationB->id, 'name' => self::ORGANIZATION_NAMES[1], 'title' => 'Global Team'],
        ], $result['data']['organizations']);
    }

    public function testUsersListIncludesOrganizations(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'operator@example.com');
        $target = $this->createUser(self::DETAIL_TARGET_USERNAME, 'detail@example.com');
        $organization = $this->createOrganization('North Team', self::ORGANIZATION_NAMES[2]);

        $this->bindOrganization($target->id, $organization->id);
        $this->bootAuthenticatedOperator($operator->id, ['list-users']);
        Yii::$app->set('request', new PluginUserTestRequest(['page' => '1', 'pageSize' => '20'], []));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $result = $controller->actionUsers();

        $targetRow = null;
        foreach ($result['data'] as $row) {
            if (($row['username'] ?? null) === self::DETAIL_TARGET_USERNAME) {
                $targetRow = $row;
                break;
            }
        }

        $this->assertSame(0, $result['code'] ?? 0);
        $this->assertNotNull($targetRow);
        $this->assertArrayHasKey('organizations', $targetRow);
        $this->assertSame([
            ['id' => $organization->id, 'name' => self::ORGANIZATION_NAMES[2], 'title' => 'North Team'],
        ], $targetRow['organizations']);
    }

    private function bootAuthenticatedOperator(int $userId, array $allowedActions): void
    {
        $webUser = new WebUser([
            'identityClass' => User::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity(User::findOne($userId));

        Yii::$app->set('user', $webUser);
        Yii::$app->set('authManager', new class($allowedActions) extends Component {
            public function __construct(private array $allowedActions, $config = [])
            {
                parent::__construct($config);
            }

            public function checkAccess($userId, $permissionName, $params = []): bool
            {
                if (str_starts_with($permissionName, 'user-management.')) {
                    $action = substr($permissionName, strlen('user-management.'));
                    return in_array($action, $this->allowedActions, true);
                }

                return false;
            }

            public function getRolesByUser($userId): array
            {
                return ['admin' => new \stdClass()];
            }

            public function getRole($roleName): object
            {
                return (object) ['name' => $roleName];
            }

            public function assign($role, $userId): void
            {
            }
        });
    }

    private function createUser(string $username, string $email): User
    {
        $user = new User();
        $user->username = $username;
        $user->nickname = $username;
        $user->email = $email;
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash('secret123');
        $user->status = 10;
        $user->created_at = time();
        $user->updated_at = time();
        $user->save(false);

        return $user;
    }

    private function createOrganization(string $title, string $name): Organization
    {
        $organization = new Organization([
            'title' => $title,
            'name' => $name,
        ]);
        $organization->save(false);

        return $organization;
    }

    private function bindOrganization(int $userId, int $organizationId): void
    {
        $binding = new UserOrganization([
            'user_id' => $userId,
            'organization_id' => $organizationId,
        ]);
        $binding->save(false);
    }

    private function cleanupFixtures(): void
    {
        $userIds = User::find()
            ->select('id')
            ->where(['username' => [
                self::OPERATOR_USERNAME,
                self::DETAIL_TARGET_USERNAME,
                self::UPDATE_TARGET_USERNAME,
                self::CREATE_TARGET_USERNAME,
            ]])
            ->column();

        if (!empty($userIds)) {
            UserOrganization::deleteAll(['user_id' => $userIds]);
            UserInfo::deleteAll(['user_id' => $userIds]);
            User::deleteAll(['id' => $userIds]);
        }

        Organization::deleteAll(['name' => self::ORGANIZATION_NAMES]);
    }
}

final class PluginUserTestRequest extends \yii\web\Request
{
    public function __construct(
        private array $queryParams,
        private array $bodyParams,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$name] ?? $defaultValue;
    }

    public function getBodyParam($name, $defaultValue = null)
    {
        return $this->bodyParams[$name] ?? $defaultValue;
    }

    public function getBodyParams()
    {
        return $this->bodyParams;
    }
}
