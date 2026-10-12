<?php

namespace tests\unit\services;

use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\models\User;
use api\modules\v1\services\EmailService;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\services\LegacyEmailChallengeRecorder;
use PHPUnit\Framework\TestCase;
use Yii;

/** Opt-in real isolated Redis checks; never flush or inspect unrelated keys. */
class IdentityLegacyEmailAssociationTest extends TestCase
{
    private array $environment;
    private array $components;
    private $redis;
    private string $email;
    private CapturedEmailActor $actor;

    protected function setUp(): void
    {
        if (getenv('IDENTITY_TEST_REDIS') !== '1') $this->markTestSkipped('Explicit isolated Redis test opt-in required.');
        $this->environment = ['IDENTITY_DATA_SOURCE' => getenv('IDENTITY_DATA_SOURCE'),
            'IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES' => getenv('IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES')];
        putenv('IDENTITY_DATA_SOURCE=legacy'); putenv('IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES=true');
        $this->components = array_intersect_key(Yii::$app->getComponents(), array_flip(['redis', 'user']));
        $this->redis = class_exists('LegacyEmailRpcRedis') ? new \LegacyEmailRpcRedis() : new \yii\redis\Connection([
            'hostname' => getenv('IDENTITY_TEST_REDIS_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('IDENTITY_TEST_REDIS_PORT') ?: 16379), 'database' => 0]);
        $this->redis->executeCommand('PING');
        $this->email = 'identity-association-' . bin2hex(random_bytes(8)) . '@example.test';
        $user = new CapturedEmailUser(); $user->id = 7; $user->email = 'old@example.test'; $user->status = 10;
        $this->actor = new CapturedEmailActor(); $this->actor->identity = $user;
        Yii::$app->set('redis', $this->redis); Yii::$app->set('user', $this->actor);
    }

    protected function tearDown(): void
    {
        if (!isset($this->email)) return;
        $this->redis->executeCommand('DEL', array_merge(RedisKeyManager::getAllVerificationKeys($this->email), [
            RedisKeyManager::getRateLimitKey($this->email, 'send_verification'), LegacyEmailChallengeRecorder::key($this->email)]));
        foreach ($this->components as $name => $value) Yii::$app->set($name, $value);
        foreach ($this->environment as $name => $value) putenv($value === false ? $name : $name . '=' . $value);
    }

    public function testAuthenticatedCodeKeepsOriginalPayloadAndExactRedisDeadline(): void
    {
        $this->assertTrue($this->service()->sendVerificationCode($this->email));
        $codeKey = RedisKeyManager::getVerificationCodeKey($this->email);
        $payload = json_decode($this->redis->executeCommand('GET', [$codeKey]), true);
        $proof = $this->proof();
        $this->assertSame(['code', 'created_at'], array_keys($payload));
        $this->assertSame(7, $proof['user_id']);
        $this->assertSame('old@example.test', $proof['account_email']);
        $this->assertSame($this->email, $proof['target_email']);
        $this->assertSame(hash('sha256', $payload['code']), $proof['code_hash']);
        $this->assertSame($payload['created_at'], $proof['issued_at']);
        $this->assertSame('yii-email-code-v1', $proof['issuer']);
        $this->assertSame((int)$this->redis->executeCommand('PEXPIRETIME', [$codeKey]), (int)$proof['expires_at_ms']);
        $this->assertSame((int)$proof['expires_at_ms'], (int)$this->redis->executeCommand('PEXPIRETIME', [LegacyEmailChallengeRecorder::key($this->email)]));
        $this->assertArrayNotHasKey('code', $proof);
    }

    public function testDefaultOffKeepsLegacyIssuanceWithoutCompanion(): void
    {
        putenv('IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES=false');
        $this->assertTrue($this->service()->sendVerificationCode($this->email));
        $this->assertNotNull($this->redis->executeCommand('GET', [RedisKeyManager::getVerificationCodeKey($this->email)]));
        $this->assertNull($this->redis->executeCommand('GET', [LegacyEmailChallengeRecorder::key($this->email)]));
    }

    public function testAnonymousOverwriteRemovesEarlierAssociation(): void
    {
        $this->service()->sendVerificationCode($this->email);
        $this->assertSame(7, $this->proof()['user_id']);
        $this->redis->executeCommand('DEL', [RedisKeyManager::getRateLimitKey($this->email, 'send_verification')]);
        $this->actor->identity = null;
        $this->assertTrue($this->service()->sendVerificationCode($this->email));
        $this->assertNull($this->redis->executeCommand('GET', [LegacyEmailChallengeRecorder::key($this->email)]));
    }

    public function testFirstBindingRecordsExplicitNullAccountEmail(): void
    {
        $this->actor->identity->email = '';
        $this->service()->sendVerificationCode($this->email);
        $this->assertNull($this->proof()['account_email']);
        $this->assertSame(7, $this->proof()['user_id']);
    }

    public function testFailedDeliveryClearsCodeAndCompanion(): void
    {
        try {
            $this->service(false)->sendVerificationCode($this->email);
            $this->fail('Failed mail must fail the request.');
        } catch (\yii\web\ServerErrorHttpException $exception) {
            $this->assertNull($this->redis->executeCommand('GET', [RedisKeyManager::getVerificationCodeKey($this->email)]));
            $this->assertNull($this->redis->executeCommand('GET', [LegacyEmailChallengeRecorder::key($this->email)]));
        }
    }

    public function testSlowFailedMailCannotDeleteNewerChallenge(): void
    {
        $service = $this->service(false);
        $service->mail->callback = function () {
            $payload = json_encode(['code' => '123456', 'created_at' => time()]);
            LegacyEmailChallengeRecorder::store($this->redis, RedisKeyManager::getVerificationCodeKey($this->email), $this->email,
                $payload, EmailVerificationService::CODE_TTL, '123456', json_decode($payload, true)['created_at']);
        };
        try { $service->sendVerificationCode($this->email); $this->fail('Failed mail must fail the request.'); }
        catch (\yii\web\ServerErrorHttpException $exception) {
            $payload = json_decode($this->redis->executeCommand('GET', [RedisKeyManager::getVerificationCodeKey($this->email)]), true);
            $this->assertSame('123456', $payload['code']);
            $this->assertSame(hash('sha256', '123456'), $this->proof()['code_hash']);
        }
    }

    public function testSuccessfulConsumptionClearsAssociation(): void
    {
        $service = $this->service(); $service->sendVerificationCode($this->email);
        $this->assertTrue($service->clearCaptured($this->email));
        $this->assertNull($this->redis->executeCommand('GET', [LegacyEmailChallengeRecorder::key($this->email)]));
    }

    public function testIndependentModeNeverEnablesLegacyCapture(): void
    {
        putenv('IDENTITY_DATA_SOURCE=identity');
        $this->assertFalse(LegacyEmailChallengeRecorder::enabled());
    }

    private function service(bool $deliver = true): CapturedEmailService
    {
        $service = new CapturedEmailService(); $service->mail = new CapturedEmailMail(); $service->mail->deliver = $deliver;
        $service->setEmailDeliveryService($service->mail);
        return $service;
    }
    private function proof(): array { return json_decode($this->redis->executeCommand('GET', [LegacyEmailChallengeRecorder::key($this->email)]), true, 512, JSON_THROW_ON_ERROR); }
}
class CapturedEmailUser extends User { public function attributes() { return ['id', 'email', 'status']; } }
class CapturedEmailActor extends \yii\base\Component { public ?User $identity; public function getIdentity() { return $this->identity; } }
class CapturedEmailService extends EmailVerificationService
{
    public CapturedEmailMail $mail;
    public function clearCaptured(string $email): bool { return $this->clearVerificationData($email); }
}
class CapturedEmailMail extends EmailService
{
    public bool $deliver = true;
    public $callback;
    public function sendVerificationCode(string $email, string $code, string $locale = 'en-US', array $i18n = []): bool
    { if ($this->callback !== null) ($this->callback)(); return $this->deliver; }
}
