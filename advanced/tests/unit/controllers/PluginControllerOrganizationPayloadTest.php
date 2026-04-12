<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\base\BaseObject;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\web\User as WebUser;

final class PluginControllerOrganizationPayloadTest extends TestCase
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

    public function testVerifyTokenIncludesOrganizations(): void
    {
        $user = new PluginPayloadIdentity([
            'id' => 7,
            'username' => 'plugin-user@example.com',
            'nickname' => 'Plugin User',
            'organizations' => [
                new PluginPayloadOrganization(['id' => 3, 'title' => 'Acme Studio', 'name' => 'acme-studio']),
                new PluginPayloadOrganization(['id' => 5, 'title' => 'Global Team', 'name' => 'global-team']),
            ],
        ]);

        $webUser = new WebUser([
            'identityClass' => PluginPayloadIdentity::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity($user);

        Yii::$app->set('user', $webUser);
        Yii::$app->set('authManager', new class extends Component {
            public function getRolesByUser($userId): array
            {
                return [
                    'admin' => new \stdClass(),
                ];
            }
        });
        Yii::$app->set('response', new Response());

        $controller = new PluginController('plugin', Yii::$app->getModule('v1'));
        $result = $controller->actionVerifyToken();

        $this->assertSame(0, $result['code']);
        $this->assertArrayHasKey('organizations', $result['data']);
        $this->assertSame([
            ['id' => 3, 'name' => 'acme-studio', 'title' => 'Acme Studio'],
            ['id' => 5, 'name' => 'global-team', 'title' => 'Global Team'],
        ], $result['data']['organizations']);
    }
}

final class PluginPayloadOrganization extends BaseObject
{
    public int $id;
    public string $title;
    public string $name;
}

final class PluginPayloadIdentity extends BaseObject implements IdentityInterface
{
    public int $id;
    public string $username;
    public string $nickname;
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
