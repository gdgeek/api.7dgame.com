<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityProviderClient;
use common\models\IdentityLegacyFormBridge;
use common\models\PasswordResetRequestForm;
use common\models\ResendVerificationEmailForm;
use common\models\ResetPasswordForm;
use common\models\SignupForm;
use PHPUnit\Framework\TestCase;
use Yii;

class IdentityLegacyFormsTest extends TestCase
{
    private $environment;
    private $mailer;
    private $supportEmail;
    private array $components;
    private LegacyFormsClient $client;

    protected function setUp(): void
    {
        $this->environment = getenv('IDENTITY_DATA_SOURCE');
        putenv('IDENTITY_DATA_SOURCE=identity');
        $this->client = new LegacyFormsClient();
        Yii::$container->set(IdentityProviderClient::class, fn() => $this->client);
        $this->mailer = Yii::$app->getComponents()['mailer'] ?? null;
        Yii::$app->set('mailer', new LegacyFormsMailer());
        $this->supportEmail = Yii::$app->params['supportEmail'] ?? null;
        Yii::$app->params['supportEmail'] = 'support@example.test';
        $this->components = array_intersect_key(Yii::$app->getComponents(), array_flip(['user', 'identityAuthority']));
    }

    protected function tearDown(): void
    {
        putenv($this->environment === false ? 'IDENTITY_DATA_SOURCE' : 'IDENTITY_DATA_SOURCE=' . $this->environment);
        Yii::$container->clear(IdentityProviderClient::class);
        Yii::$app->set('mailer', $this->mailer);
        if ($this->supportEmail === null) unset(Yii::$app->params['supportEmail']);
        else Yii::$app->params['supportEmail'] = $this->supportEmail;
        foreach (['user', 'identityAuthority'] as $name) Yii::$app->set($name, $this->components[$name] ?? null);
    }

    public function testOldResetFormValidatesAndConsumesOnlyAtIdentity(): void
    {
        $form = new ResetPasswordForm('old-signed-reset-token');
        $form->password = 'new password only sent to Identity';
        $this->assertTrue($form->resetPassword());
        $this->assertSame(['validate-reset', 'reset'], array_column($this->client->calls, 'operation'));
        $this->assertSame(['token' => 'old-signed-reset-token', 'password' => $form->password], $this->client->calls[1]['payload']);
    }

    public function testResetEmailUsesIdentityTokenAndOriginalTemplate(): void
    {
        $form = new PasswordResetRequestForm(['email' => 'user@example.test']);
        $this->assertTrue($form->validate());
        $this->assertTrue($form->sendEmail());
        $this->assertSame('request-reset', $this->client->calls[0]['operation']);
        $message = Yii::$app->mailer;
        $this->assertSame('passwordResetToken-html', $message->views['html']);
        $this->assertSame('identity-generated-link-token', $message->user->password_reset_token);
        $this->assertSame('user@example.test', $message->recipient);
    }

    public function testResendVerificationUsesIdentityTokenAndOriginalTemplate(): void
    {
        $form = new ResendVerificationEmailForm(['email' => 'user@example.test']);
        $this->assertTrue($form->validate());
        $this->assertTrue($form->sendEmail());
        $this->assertSame('resend-verification', $this->client->calls[0]['operation']);
        $this->assertSame('emailVerify-html', Yii::$app->mailer->views['html']);
        $this->assertSame('identity-generated-link-token', Yii::$app->mailer->user->verification_token);
    }

    public function testSignupUniquenessIsNotDecidedByStaleBusinessProjection(): void
    {
        $rules = (new SignupForm())->rules();
        $this->assertNotContains('unique', array_column($rules, 1));
    }

    public function testAlreadyVerifiedSignupRetryDoesNotSendEmptyLink(): void
    {
        $this->client->response['status'] = 10;
        $this->client->response['token'] = null;
        $this->assertTrue((new IdentityLegacyFormBridge())->sendEmail('signup', ['username' => 'user']));
        $this->assertSame(0, Yii::$app->mailer->sent);
    }

    public function testIdentityErrorCannotFallBackToOldPasswordToken(): void
    {
        $this->client->fail = true;
        $this->expectException(\common\components\security\ServiceUnavailableHttpException::class);
        new ResetPasswordForm('old-signed-reset-token');
    }

    public function testEmailVerificationUsesOnlyAtomicallyReturnedSessionProof(): void
    {
        Yii::$app->set('identityAuthority', new FormsAuthority());
        Yii::$app->set('user', $user = new FormsBackendUser(['identityClass' => \api\modules\v1\models\User::class, 'enableSession' => false]));
        $tokens = ['accessToken' => 'same-proof-access', 'refreshToken' => 'same-proof-refresh'];
        $this->client->response['authTokens'] = $tokens;
        $this->assertSame(7, (new \common\models\VerifyEmailForm('old-verify-token'))->verifyEmail()->id);
        $this->assertSame([$tokens], $user->logins);
        $this->assertSame(['verify'], array_column($this->client->calls, 'operation'));
    }

    public function testUsedVerificationLinkWithoutProofCannotLogInAgain(): void
    {
        Yii::$app->set('identityAuthority', new FormsAuthority());
        Yii::$app->set('user', $user = new FormsBackendUser(['identityClass' => \api\modules\v1\models\User::class, 'enableSession' => false]));
        $this->assertSame(7, (new \common\models\VerifyEmailForm('already-used-verify-token'))->verifyEmail()->id);
        $this->assertSame([], $user->logins);
    }
}

class FormsMemoryUser extends \api\modules\v1\models\User
{
    public function attributes() { return ['id']; }
}
class FormsAuthority extends \api\modules\v1\services\IdentityAuthorityService
{
    public function identityById(int $id): ?\api\modules\v1\models\User { $user = new FormsMemoryUser(); $user->id = $id; return $user; }
}
class FormsBackendUser extends \common\components\IdentityBackendUser
{
    public array $logins = [];
    public function loginWithIdentityTokens(array $tokens, int $duration = 0): bool { $this->logins[] = $tokens; return true; }
}

class LegacyFormsClient extends IdentityProviderClient
{
    public array $calls = [];
    public bool $fail = false;
    public array $response = ['userId' => 7, 'username' => 'user', 'email' => 'user@example.test', 'status' => 9, 'token' => 'identity-generated-link-token'];
    public function legacyEmailLink(string $operation, array $payload, array $context = []): array
    {
        $this->calls[] = compact('operation', 'payload', 'context');
        if ($this->fail) throw new \common\components\security\ServiceUnavailableHttpException('Identity unavailable');
        return $this->response;
    }
    public function issueUserToken(int $legacyUserId, array $context = []): array { throw new \LogicException('Verification must not mint a session by user ID.'); }
}

class LegacyFormsMailer extends \yii\base\Component
{
    public array $views = [];
    public ?object $user = null;
    public ?string $recipient = null;
    public int $sent = 0;
    public function compose($views, $params) { $this->views = $views; $this->user = $params['user']; return $this; }
    public function setFrom($from) { return $this; }
    public function setTo($to) { $this->recipient = $to; return $this; }
    public function setSubject($subject) { return $this; }
    public function send() { ++$this->sent; return true; }
}
