<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\UserController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\BaseObject;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\web\User as WebUser;

final class UserControllerOrganizationPayloadTest extends TestCase
{
    private $originalUserComponent;
    private $originalAuthManager;
    private $originalResponseComponent;
    private bool $hadUserComponent = false;
    private bool $hadAuthManager = false;
    private bool $hadResponseComponent = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadUserComponent = Yii::$app->has('user', true);
        $this->hadAuthManager = Yii::$app->has('authManager', true);
        $this->hadResponseComponent = Yii::$app->has('response', true);
        $this->originalUserComponent = $this->hadUserComponent ? Yii::$app->get('user') : null;
        $this->originalAuthManager = $this->hadAuthManager ? Yii::$app->get('authManager') : null;
        $this->originalResponseComponent = $this->hadResponseComponent ? Yii::$app->get('response') : null;
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->hadUserComponent ? $this->originalUserComponent : null);
        Yii::$app->set('authManager', $this->hadAuthManager ? $this->originalAuthManager : null);
        Yii::$app->set('response', $this->hadResponseComponent ? $this->originalResponseComponent : null);
        parent::tearDown();
    }

    public function testInfoIncludesOrganizations(): void
    {
        $user = new UserInfoPayloadIdentity([
            'id' => 12,
            'username' => 'user@example.com',
            'nickname' => 'User Test',
            'email' => 'user@example.com',
            'email_verified_at' => 1710000000,
            'userInfo' => ['user_id' => 12],
            'data' => ['username' => 'user@example.com', 'nickname' => 'User Test'],
            'roles' => ['admin'],
            'organizations' => [
                new UserInfoPayloadOrganization(['id' => 9, 'title' => 'North Team', 'name' => 'north-team']),
                new UserInfoPayloadOrganization(['id' => 10, 'title' => 'South Team', 'name' => 'south-team']),
            ],
        ]);

        $webUser = new WebUser([
            'identityClass' => UserInfoPayloadIdentity::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity($user);

        Yii::$app->set('user', $webUser);
        Yii::$app->set('response', new Response());

        $controller = new UserController('user', Yii::$app->getModule('v1'));
        $result = $controller->actionInfo();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('organizations', $result['data']);
        $this->assertSame([
            ['id' => 9, 'name' => 'north-team', 'title' => 'North Team'],
            ['id' => 10, 'name' => 'south-team', 'title' => 'South Team'],
        ], $result['data']['organizations']);
    }
}

final class UserInfoPayloadOrganization extends BaseObject
{
    public int $id;
    public string $title;
    public string $name;
}

final class UserInfoPayloadIdentity extends BaseObject implements IdentityInterface
{
    public int $id;
    public string $username;
    public string $nickname;
    public string $email;
    public int $email_verified_at;
    public array $userInfo = [];
    public array $data = [];
    public array $roles = [];
    public array $organizations = [];

    public static function findIdentity($id): ?IdentityInterface
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
