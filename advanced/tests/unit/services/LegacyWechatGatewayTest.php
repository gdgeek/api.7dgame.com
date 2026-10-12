<?php

namespace tests\unit\services;

use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\LegacyWechatGateway;
use PHPUnit\Framework\TestCase;

class LegacyWechatGatewayTest extends TestCase
{
    public function testOldQrRoutesKeepTicketAndUrlShapes(): void
    {
        $client = new LegacyWechatRecordingClient();
        $client->responses[] = ['status' => 200, 'body' => ['qrcode' => ['ticket' => 'ticket', 'url' => 'https://qr.test'], 'token' => 'scene', 'lifetime' => 3600]];
        $client->responses[] = $client->responses[0];
        $gateway = new LegacyWechatGateway($client);
        $this->assertSame(['qrcode' => 'ticket', 'token' => 'scene', 'lifetime' => 3600], $gateway->forward('wechat/qrcode', [], [], [])['body']);
        $this->assertSame(['qrcode' => 'https://qr.test', 'token' => 'scene'], $gateway->forward('site/wechat-qrcode', [], [], [])['body']);
        $this->assertSame('/v1/wechat/qrcode', $client->calls[0]['path']);
    }

    public function testPendingAndSignupPollKeepOldResponse(): void
    {
        $client = new LegacyWechatRecordingClient();
        $client->responses = [['status' => 200, 'body' => ['success' => false, 'message' => 'pending']],
            ['status' => 200, 'body' => ['success' => true, 'message' => 'signup', 'token' => 'identity-challenge']]];
        $gateway = new LegacyWechatGateway($client);
        $this->assertInstanceOf(\stdClass::class, $gateway->forward('wechat/openid', [], ['token' => 'scene'], [])['body']);
        $this->assertSame(['token' => 'identity-challenge'], $gateway->forward('site/wechat-openid', [], ['token' => 'scene'], [])['body']);
    }

    public function testScannedUserTokenIsIssuedAndVerifiedOnlyByIdentity(): void
    {
        $client = new LegacyWechatRecordingClient();
        $client->responses = [['status' => 200, 'body' => ['success' => true, 'message' => 'signin', 'token' => 'scene']],
            ['status' => 200, 'body' => ['token' => ['accessToken' => 'identity-access']]]];
        $result = (new LegacyWechatGateway($client))->forward('wechat/openid', [], ['token' => 'scene'], []);
        $this->assertSame(['token' => 'scene', 'user' => ['nickname' => 'Nickname', 'username' => 'user', 'roles' => ['user']],
            'access_token' => 'identity-access'], $result['body']);
        $this->assertSame('/v1/wechat/login', $client->calls[1]['path']);
        $this->assertSame('identity-access', $client->verified);
    }

    public function testSignupAndBindingPreserveHistoricalCredentialRequestAndTokenShape(): void
    {
        $client = new LegacyWechatRecordingClient();
        $client->responses = [['status' => 200, 'body' => ['token' => ['token' => 'identity-access']]],
            ['status' => 200, 'body' => ['access_token' => 'binding-access', 'code' => 20000]]];
        $body = ['username' => 'user', 'password' => 'password', 'token' => 'confirmed-scene'];
        $gateway = new LegacyWechatGateway($client);
        $this->assertSame(['access_token' => 'identity-access', 'code' => 20000], $gateway->forward('wechat/signup', $body, [], [])['body']);
        $this->assertSame(['access_token' => 'binding-access', 'code' => 20000], $gateway->forward('wechat/binding', $body, [], [])['body']);
        $this->assertSame($body, $client->calls[0]['payload']);
        $this->assertSame('/v1/wechat/binding', $client->calls[1]['path']);
    }

    public function testIdentityFailureNeverContinuesToTokenIssuance(): void
    {
        $client = new LegacyWechatRecordingClient();
        $client->responses = [['status' => 503, 'body' => ['code' => 'IDENTITY_UNAVAILABLE']]];
        $this->assertSame(503, (new LegacyWechatGateway($client))->forward('wechat/openid', [], ['token' => 'scene'], [])['status']);
        $this->assertCount(1, $client->calls);
        $this->assertNull($client->verified);
    }
}

class LegacyWechatRecordingClient extends IdentityProviderClient
{
    public array $responses = [];
    public array $calls = [];
    public ?string $verified = null;
    public function proxyAccountLifecycle(string $method, string $path, ?array $payload = null, array $query = [], array $context = []): array
    {
        $this->calls[] = compact('method', 'path', 'payload', 'query', 'context');
        return array_shift($this->responses);
    }
    public function verifyIdentityAccessToken(string $token): array
    {
        $this->verified = $token;
        return ['id' => 7, 'username' => 'user', 'nickname' => 'Nickname', 'roles' => ['user'], 'status' => 10];
    }
}
