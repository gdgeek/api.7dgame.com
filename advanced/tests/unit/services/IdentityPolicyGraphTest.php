<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityAuthorityService;
use api\modules\v1\services\IdentityProviderClient;
use common\components\IdentityBackendUser;
use common\components\IdentityDbManager;
use PHPUnit\Framework\TestCase;
use Yii;

class IdentityPolicyGraphTest extends TestCase
{
    private $environment;
    private $authority;
    private $user;
    private GraphClient $client;

    protected function setUp(): void
    {
        $this->environment = getenv('IDENTITY_DATA_SOURCE'); putenv('IDENTITY_DATA_SOURCE=identity');
        $this->authority = Yii::$app->getComponents()['identityAuthority'] ?? null;
        $this->user = Yii::$app->getComponents()['user'];
        $this->client = new GraphClient();
        Yii::$container->set(IdentityProviderClient::class, fn() => $this->client);
        Yii::$app->set('identityAuthority', new IdentityAuthorityService(['client' => $this->client]));
        Yii::$app->set('user', new GraphBackendUser(['identityClass' => \api\modules\v1\models\User::class, 'enableSession' => false]));
    }

    protected function tearDown(): void
    {
        putenv($this->environment === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $this->environment);
        Yii::$container->clear(IdentityProviderClient::class);
        Yii::$app->set('identityAuthority', $this->authority); Yii::$app->set('user', $this->user);
    }

    public function testCurrentPrivateGraphExecutesInheritedBusinessRuleInPhp(): void
    {
        $manager = new IdentityDbManager();
        $this->assertTrue($manager->checkAccess(7, 'edit', ['ownerId' => 7]));
        $this->assertFalse($manager->checkAccess(7, 'edit', ['ownerId' => 8]));
        $this->assertSame(['scoped', 'edit'], array_keys($manager->getPermissionsByRole('manager')));
    }

    public function testIdentityDenyCannotBeOverriddenByPassingRule(): void
    {
        $this->client->allowed = false;
        $this->assertFalse((new IdentityDbManager())->checkAccess(7, 'edit', ['ownerId' => 7]));
        $this->assertSame(0, $this->client->graphReads);
    }

    public function testEffectivePermissionIsNotFlattenedPastIntermediateRule(): void
    {
        $this->assertFalse((new IdentityDbManager())->checkAccess(7, 'edit', ['ownerId' => 999]));
    }

    public function testGraphCrudAndAssignmentsUseIdentityRpcAndRefreshSnapshot(): void
    {
        $manager = new IdentityDbManager();
        $this->assertNotNull($manager->getRole('manager'));
        $role = $manager->createRole('new-role');
        $this->assertTrue($manager->add($role));
        $this->assertSame('add-item', $this->client->mutations[0]['action']);
        $this->assertSame('new-role', $this->client->mutations[0]['item']['name']);
        $this->assertSame('new-role', $manager->getRole('new-role')->name);
        $this->assertSame('new-role', $manager->assign($role, 7)->roleName);
        $this->assertSame('assign', $this->client->mutations[1]['action']);
        $this->assertSame('verified-admin-access', $this->client->actor);
        $this->assertSame(2, $this->client->graphReads);
    }

    public function testGraphEdgeCycleIsRejectedBeforeRemoteMutation(): void
    {
        $manager = new IdentityDbManager();
        $this->assertFalse($manager->canAddChild($manager->getPermission('edit'), $manager->getRole('manager')));
        $this->assertFalse($manager->canAddChild($manager->getRole('manager'), $manager->getRole('manager')));
    }

    public function testPolicyServiceFailureNeverReadsOldGraph(): void
    {
        $this->client->fail = true;
        $this->expectException(\common\components\security\ServiceUnavailableHttpException::class);
        (new IdentityDbManager())->getRole('manager');
    }

    public function testBulkRemovalCannotReachOldPolicyTables(): void
    {
        $this->expectException(\yii\base\InvalidCallException::class);
        (new IdentityDbManager())->removeAllAssignments();
    }
}

class GraphRule extends \yii\rbac\Rule
{
    public $name = 'ownership';
    public function execute($user, $item, $params) { return ($params['ownerId'] ?? null) === $user; }
}
class GraphBackendUser extends IdentityBackendUser
{
    public function identityAccessToken(): ?string { return 'verified-admin-access'; }
}
class GraphClient extends IdentityProviderClient
{
    public bool $allowed = true;
    public bool $fail = false;
    public int $graphReads = 0;
    public array $mutations = [];
    public ?string $actor = null;
    private array $extraItems = [];
    public function identityPolicyGraph(): array
    {
        ++$this->graphReads;
        if ($this->fail) throw new \common\components\security\ServiceUnavailableHttpException('Identity unavailable');
        return ['items' => array_merge([
            ['name' => 'manager', 'type' => 1], ['name' => 'scoped', 'type' => 2, 'rule_name' => 'ownership'], ['name' => 'edit', 'type' => 2],
        ], $this->extraItems), 'children' => [['parent' => 'manager', 'child' => 'scoped'], ['parent' => 'scoped', 'child' => 'edit']],
            'rules' => [['name' => 'ownership', 'dataBase64' => base64_encode(serialize(new GraphRule()))]]];
    }
    public function identitySubject(int $id): array
    {
        return ['id' => $id, 'username' => 'user', 'status' => 10, 'roles' => ['manager'], 'permissions' => ['edit'], 'directPermissions' => [], 'organizations' => []];
    }
    public function identityPermission(int $id, string $permission): bool { return $this->allowed; }
    public function legacyAdmin(string $operation, array $payload = [], ?string $accessToken = null): array
    {
        $this->actor = $accessToken; $this->mutations[] = $payload;
        if (($payload['action'] ?? null) === 'add-item') $this->extraItems[] = $payload['item'];
        return ['success' => true, 'changed' => true];
    }
}
