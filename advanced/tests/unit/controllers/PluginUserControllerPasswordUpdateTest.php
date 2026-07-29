<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginUserController;
use api\modules\v1\models\User;
use api\modules\v1\RefreshToken;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\BeforeSaveEvent;
use yii\web\Response;
use yii\web\User as WebUser;

final class PluginUserControllerPasswordUpdateTest extends TestCase
{
    private const CURRENT_PASSWORD = 'N9#VaultSafe';
    private const NEW_PASSWORD = 'W8!RotatedSafe';
    private const OPERATOR_USERNAME = 'plugin-user-password-operator';
    private const TARGET_USERNAME = 'plugin-user-password-target';

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
        Event::off(User::class, ActiveRecord::EVENT_BEFORE_UPDATE);
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->set('authManager', $this->originalAuthManager);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->set('request', $this->originalRequestComponent);
        $this->cleanupFixtures();
        parent::tearDown();
    }

    public function testPasswordUpdatePersistsAndRevokesOnlyTargetRefreshSessions(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'password-operator@example.com');
        $target = $this->createUser(self::TARGET_USERNAME, 'password-target@example.com');
        $this->createRefreshToken((int)$operator->id, 'operator-session');
        $this->createRefreshToken((int)$target->id, 'target-session');

        $result = $this->runUpdate($operator, [
            'id' => $target->id,
            'password' => self::NEW_PASSWORD,
        ]);

        $target->refresh();
        $this->assertSame(0, $result['code']);
        $this->assertTrue(Yii::$app->security->validatePassword(self::NEW_PASSWORD, $target->password_hash));
        $this->assertSame(0, (int)RefreshToken::find()->where(['user_id' => $target->id])->count());
        $this->assertSame(1, (int)RefreshToken::find()->where(['user_id' => $operator->id])->count());
    }

    public function testProfileOnlyUpdateDoesNotRevokeTargetRefreshSessions(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'password-operator@example.com');
        $target = $this->createUser(self::TARGET_USERNAME, 'password-target@example.com');
        $this->createRefreshToken((int)$target->id, 'target-session');

        $result = $this->runUpdate($operator, [
            'id' => $target->id,
            'nickname' => 'Profile Only Update',
        ]);

        $target->refresh();
        $this->assertSame(0, $result['code']);
        $this->assertSame('Profile Only Update', $target->nickname);
        $this->assertSame(1, (int)RefreshToken::find()->where(['user_id' => $target->id])->count());
    }

    public function testFailedUserSaveRollsBackAndReturnsServerError(): void
    {
        $operator = $this->createUser(self::OPERATOR_USERNAME, 'password-operator@example.com');
        $target = $this->createUser(self::TARGET_USERNAME, 'password-target@example.com');
        $originalNickname = $target->nickname;

        Event::on(
            User::class,
            ActiveRecord::EVENT_BEFORE_UPDATE,
            static function (BeforeSaveEvent $event) use ($target): void {
                if ((int)$event->sender->id === (int)$target->id) {
                    $event->isValid = false;
                }
            }
        );

        $result = $this->runUpdate($operator, [
            'id' => $target->id,
            'nickname' => 'Must Not Persist',
        ]);

        $target->refresh();
        $this->assertSame(500, Yii::$app->response->statusCode);
        $this->assertSame(5000, $result['code']);
        $this->assertSame($originalNickname, $target->nickname);
    }

    private function runUpdate(User $operator, array $body): array
    {
        $this->bootAuthenticatedOperator((int)$operator->id);
        Yii::$app->set('request', new PluginUserPasswordTestRequest($body));
        Yii::$app->set('response', new Response());

        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        return $controller->actionUpdateUser();
    }

    private function bootAuthenticatedOperator(int $userId): void
    {
        $webUser = new WebUser([
            'identityClass' => User::class,
            'enableSession' => false,
        ]);
        $webUser->setIdentity(User::findOne($userId));

        Yii::$app->set('user', $webUser);
        Yii::$app->set('authManager', new class extends Component {
            public function checkAccess($userId, $permissionName, $params = []): bool
            {
                return $permissionName === 'user-management.update-user';
            }

            public function getRolesByUser($userId): array
            {
                return ['admin' => new \stdClass()];
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
        $user->password_hash = Yii::$app->security->generatePasswordHash(self::CURRENT_PASSWORD);
        $user->status = 10;
        $user->created_at = time();
        $user->updated_at = time();
        $this->assertTrue($user->save(false));

        return $user;
    }

    private function createRefreshToken(int $userId, string $sessionId): RefreshToken
    {
        $token = new RefreshToken();
        $token->user_id = $userId;
        $token->key = RefreshToken::hashToken($sessionId . '-refresh');
        $token->session_id = $sessionId;
        $token->created_at = time();
        $token->expires_at = time() + RefreshToken::expirySeconds();
        $this->assertTrue($token->save(false));

        return $token;
    }

    private function cleanupFixtures(): void
    {
        $userIds = User::find()
            ->select('id')
            ->where(['username' => [self::OPERATOR_USERNAME, self::TARGET_USERNAME]])
            ->column();

        if ($userIds !== []) {
            RefreshToken::deleteAll(['user_id' => $userIds]);
            User::deleteAll(['id' => $userIds]);
        }
    }
}

final class PluginUserPasswordTestRequest extends \yii\web\Request
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
