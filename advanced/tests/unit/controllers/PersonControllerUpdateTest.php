<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PersonController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\BaseObject;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\web\User as WebUser;

final class PersonControllerUpdateTest extends TestCase
{
    private $originalUserComponent;
    private $originalResponseComponent;
    private $originalRequestComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalUserComponent = Yii::$app->get('user', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->originalRequestComponent = Yii::$app->get('request', false);
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->set('request', $this->originalRequestComponent);
        parent::tearDown();
    }

    public function testUpdateChangesNicknameButKeepsUsernameUntouched(): void
    {
        $target = new FakeManagedPerson([
            'id' => 9,
            'username' => 'target@example.com',
            'nickname' => 'Before Nickname',
            'roles' => ['user'],
        ]);

        $webUser = new WebUser([
            'identityClass' => FakePersonIdentity::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity(new FakePersonIdentity([
            'id' => 1,
            'roles' => ['admin'],
        ]));

        Yii::$app->set('user', $webUser);
        Yii::$app->set('request', new PersonUpdateTestRequest([
            'nickname' => 'After Nickname',
            'username' => 'should-not-change@example.com',
        ]));
        Yii::$app->set('response', new Response());

        $controller = new PersonControllerForTest('people', Yii::$app->getModule('v1'), $target);
        $result = $controller->actionUpdate($target->id);

        $this->assertTrue($result['success']);
        $this->assertSame('After Nickname', $target->nickname);
        $this->assertSame('target@example.com', $target->username);
        $this->assertSame([['nickname']], $target->savedAttributes);
    }

    public function testNormalizeOrganizationsForResponseSkipsMalformedItems(): void
    {
        $controller = new PersonControllerForTest(
            'people',
            Yii::$app->getModule('v1'),
            new FakeManagedPerson([
                'id' => 9,
                'username' => 'target@example.com',
                'nickname' => 'Before Nickname',
                'roles' => ['user'],
            ])
        );

        $result = $controller->normalizeOrganizationsForTest([
            (object) [
                'id' => 11,
                'name' => 'north-team',
                'title' => 'North Team',
            ],
            (object) [
                'id' => 12,
                'name' => 'broken-team',
                'title' => null,
            ],
        ]);

        $this->assertSame([
            [
                'id' => 11,
                'name' => 'north-team',
                'title' => 'North Team',
            ],
        ], $result);
    }
}

final class PersonControllerForTest extends PersonController
{
    public function __construct($id, $module, private FakeManagedPerson $managedPerson, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    protected function findManagedUserById(int $id)
    {
        return $id === $this->managedPerson->id ? $this->managedPerson : null;
    }

    public function normalizeOrganizationsForTest(iterable $organizations): array
    {
        return $this->normalizeOrganizationsForResponse($organizations);
    }
}

final class FakeManagedPerson extends BaseObject
{
    public int $id;
    public string $username;
    public string $nickname;
    public array $roles = [];
    public array $savedAttributes = [];

    public function save($runValidation = true, $attributeNames = null): bool
    {
        $this->savedAttributes[] = $attributeNames;
        return true;
    }
}

final class FakePersonIdentity extends BaseObject implements IdentityInterface
{
    public int $id;
    public array $roles = [];

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

final class PersonUpdateTestRequest extends \yii\web\Request
{
    public function __construct(private array $bodyParams, $config = [])
    {
        parent::__construct($config);
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
