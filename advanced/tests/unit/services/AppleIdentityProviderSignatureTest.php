<?php

namespace tests\unit\services;

use api\modules\v1\services\AppleIdentityProvider;
use api\modules\v1\services\IdentityProviderClient;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use yii\web\UnauthorizedHttpException;

/** Real RSA/JWKS verification; only the external code exchange and JWKS HTTP are mocked. */
class AppleIdentityProviderSignatureTest extends TestCase
{
    private array $environment = [];
    private string $privateKey = '';
    private array $jwks;
    private string $keyPath;

    protected function setUp(): void
    {
        if (!class_exists(JWT::class) || !extension_loaded('openssl')) {
            $this->markTestSkipped('Installed Firebase JWT and OpenSSL are required for Apple signature fixtures.');
        }
        foreach (['ENABLE_APPLE_LOGIN', 'IDENTITY_APPLE_CLIENT_IDS', 'IDENTITY_APPLE_REDIRECT_URIS',
            'IDENTITY_APPLE_KEY_ENV_NAMES', 'APPLE_TEAM_ID', 'APPLE_KEY_ID', 'APPLE_AUTH_KEY'] as $name) {
            $this->environment[$name] = getenv($name);
        }
        $fixture = require dirname(__DIR__, 2) . '/fixtures/apple-signature-key.php';
        $this->privateKey = $fixture['privateKey'];
        $this->jwks = $fixture['jwks'];
        $this->keyPath = tempnam(sys_get_temp_dir(), 'apple-provider-fixture-');
        file_put_contents($this->keyPath, $this->privateKey);
        foreach (['ENABLE_APPLE_LOGIN' => 'true', 'IDENTITY_APPLE_CLIENT_IDS' => 'com.example.apple',
            'IDENTITY_APPLE_REDIRECT_URIS' => 'https://example.test/apple', 'IDENTITY_APPLE_KEY_ENV_NAMES' => 'APPLE_KEY_ID',
            'APPLE_TEAM_ID' => 'fixture-team', 'APPLE_KEY_ID' => 'fixture-key', 'APPLE_AUTH_KEY' => $this->keyPath] as $name => $value) {
            putenv($name . '=' . $value);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->environment as $name => $value) {
            putenv($value === false ? $name : $name . '=' . $value);
        }
        if (isset($this->keyPath)) {
            unlink($this->keyPath);
        }
    }

    public function testLocallySignedAppleTokenUsesVerifiedSubjectAndClaims(): void
    {
        $client = new AppleSignatureRecordingClient();
        $provider = $this->provider($this->token(), $client);
        $result = $provider->forward('apple-id', $this->request());
        $this->assertSame(200, $result['status']);
        $this->assertSame(1, $provider->exchanges);
        $this->assertSame('verified-subject', $client->payload['subject']);
        $this->assertSame('verified@example.test', $client->payload['email']);
        $this->assertTrue($client->payload['emailVerified']);
    }

    public function testTamperedTokenCannotReachIdentityAuthority(): void
    {
        $parts = explode('.', $this->token());
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $claims['sub'] = 'forged-subject';
        $parts[1] = self::base64url(json_encode($claims));
        $client = new AppleSignatureRecordingClient();
        try {
            $this->provider(implode('.', $parts), $client)->forward('apple-id', $this->request());
            $this->fail('A tampered id_token cannot reach Identity.');
        } catch (UnauthorizedHttpException $exception) {
            $this->assertSame([], $client->payload);
        }
    }

    public function testUnknownSigningKeyFailsClosed(): void
    {
        $token = JWT::encode($this->claims(), $this->privateKey, 'RS256', 'untrusted-key');
        $this->expectException(UnauthorizedHttpException::class);
        $this->provider($token)->forward('apple-id', $this->request());
    }

    public function testSignedTokenForAnotherApplicationIsRejected(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->provider($this->token(['aud' => 'another-app']))->forward('apple-id', $this->request());
    }

    public function testExpiredSignedTokenIsRejected(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->provider($this->token(['exp' => time() - 120]))->forward('apple-id', $this->request());
    }

    private function provider(string $token, ?AppleSignatureRecordingClient $client = null): AppleSignatureProvider
    {
        return new AppleSignatureProvider(['idToken' => $token, 'jwks' => $this->jwks,
            'client' => $client ?? new AppleSignatureRecordingClient()]);
    }

    private function claims(): array
    {
        return ['iss' => 'https://appleid.apple.com', 'aud' => 'com.example.apple', 'sub' => 'verified-subject',
            'exp' => time() + 300, 'iat' => time(), 'email' => 'verified@example.test', 'email_verified' => true];
    }

    private function token(array $override = []): string
    {
        return JWT::encode(array_merge($this->claims(), $override), $this->privateKey, 'RS256', 'local-apple-fixture');
    }

    private function request(): array
    {
        return ['data' => ['userData' => ['aud' => 'com.example.apple', 'sub' => 'untrusted-client-claim'],
            'authorization' => ['code' => 'mock-single-use-code']], 'url' => 'https://example.test/apple', 'key' => 'APPLE_KEY_ID'];
    }

    private static function base64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

class AppleSignatureRecordingClient extends IdentityProviderClient
{
    public array $payload = [];
    public function verifiedApple(string $operation, array $payload, array $context = []): array
    {
        $this->payload = $payload;
        return ['status' => 200, 'body' => ['apple_id' => $payload['subject']]];
    }
}

class AppleSignatureProvider extends AppleIdentityProvider
{
    public string $idToken;
    public array $jwks;
    public int $exchanges = 0;
    protected function createOAuthProvider(array $configuration): object
    {
        return new class($this) {
            public function __construct(private AppleSignatureProvider $owner) {}
            public function getAccessToken($grant, $payload) {
                if ($grant !== 'authorization_code' || $payload !== ['code' => 'mock-single-use-code']) {
                    throw new \RuntimeException('Unexpected provider exchange request.');
                }
                ++$this->owner->exchanges;
                return new class($this->owner->idToken) {
                    public function __construct(private string $token) {}
                    public function getIdToken() { return $this->token; }
                };
            }
            public function getResourceOwner($token) {
                return new class {
                    public function getId() { return 'verified-subject'; }
                    public function getFirstName() { return 'Fixture'; }
                    public function getLastName() { return 'Profile'; }
                };
            }
        };
    }
    protected function fetchAppleJwks(object $provider): array { return $this->jwks; }
}
