<?php

namespace tests\unit\services;

use api\modules\v1\services\IamAuthorizationReadService;
use api\modules\v1\services\IdentityProviderClient;
use PHPUnit\Framework\TestCase;
use Yii;

final class IamAuthorizationReadServiceTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $environment = [];

    protected function tearDown(): void
    {
        foreach ($this->environment as $key => $value) {
            if ($value === false) {
                putenv($key);
                continue;
            }

            putenv($key . '=' . $value);
        }

        parent::tearDown();
    }

    public function testDefaultOffReturnsLegacyWithoutCallingIdentity(): void
    {
        $this->setEnvironment('IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED', 'false');
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamAuthzResolve(array $input): ?array
            {
                throw new \RuntimeException('Identity must not be called while route integration is off.');
            }
        });

        $this->assertTrue($service->decide($this->user(), 'organization.list', true, 'route', 'organization.list'));
        $this->assertFalse($service->decide($this->user(), 'organization.update', false, 'route', 'organization.update'));
    }

    public function testUsesSingleIdentityDecisionAndLogsOnlySafeEvidence(): void
    {
        $this->setEnvironment('IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED', 'true');
        $client = new class extends IdentityProviderClient {
            public array $lastInput = [];

            public function iamAuthzResolve(array $input): ?array
            {
                $this->lastInput = $input;
                return [
                    'selection' => [
                        'subjectHash' => '0123456789abcdef',
                        'configuredMode' => 'identity-primary',
                        'rolloutMode' => 'allowlist',
                        'selectedForIdentityPrimary' => true,
                    ],
                    'outcome' => [
                        'decision' => 'allow',
                        'responseSource' => 'identity',
                        'fallbackUsed' => false,
                        'failClosed' => false,
                    ],
                    'evidence' => [
                        'permissionHash' => 'fedcba9876543210',
                        'requestKeyHash' => '0011223344556677',
                        'severity' => 'none',
                        'classification' => 'match',
                    ],
                ];
            }
        };
        $service = $this->serviceWithClient($client);

        $entries = $this->captureAuthzLogs(function () use ($service): void {
            $this->assertTrue($service->decide(
                $this->user(),
                'user-management.users',
                true,
                'plugin',
                'plugin-user.users'
            ));
        });

        $this->assertSame(42, $client->lastInput['subject']['legacyUserId']);
        $this->assertSame('test-operator', $client->lastInput['subject']['username']);
        $this->assertSame('allow', $client->lastInput['legacyDecision']);
        $this->assertSame('legacy-rbac-v1', $client->lastInput['legacyPolicyVersion']);

        $this->assertCount(1, $entries);
        $this->assertSame('authorization.route-decision', $entries[0]['event']);
        $this->assertSame('identity', $entries[0]['responseSource']);
        $this->assertSame('allow', $entries[0]['decision']);
        $encoded = (string)json_encode($entries);
        $this->assertStringNotContainsString('test-operator', $encoded);
        $this->assertStringNotContainsString('user-management.users', $encoded);
        $this->assertStringNotContainsString('plugin-user.users', $encoded);
    }

    public function testHonorsFailClosedIdentityDecisionWithoutPermissionUnion(): void
    {
        $this->setEnvironment('IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED', 'true');
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamAuthzResolve(array $input): ?array
            {
                return [
                    'selection' => [
                        'subjectHash' => '0123456789abcdef',
                        'configuredMode' => 'identity-primary',
                        'rolloutMode' => 'allowlist',
                        'selectedForIdentityPrimary' => true,
                    ],
                    'outcome' => [
                        'decision' => 'allow',
                        'responseSource' => 'identity',
                        'fallbackUsed' => false,
                        'failClosed' => false,
                    ],
                    'evidence' => [
                        'permissionHash' => 'fedcba9876543210',
                        'requestKeyHash' => null,
                        'severity' => 'p0',
                        'classification' => 'legacy_deny_identity_allow',
                    ],
                ];
            }
        });

        $this->assertFalse($service->decide($this->user(), 'organization.update', true));
    }

    public function testTransportFailureUsesOnlyExplicitFallback(): void
    {
        $this->setEnvironment('IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED', 'true');
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamAuthzResolve(array $input): ?array
            {
                throw new \RuntimeException('transport failure');
            }
        });

        $this->setEnvironment('IDENTITY_IAM_AUTHZ_FALLBACK_ENABLED', 'true');
        $this->assertTrue($service->decide($this->user(), 'organization.list', true));

        $this->setEnvironment('IDENTITY_IAM_AUTHZ_FALLBACK_ENABLED', 'false');
        $this->assertFalse($service->decide($this->user(), 'organization.list', true));
    }

    private function user(): object
    {
        return (object)['id' => 42, 'username' => 'test-operator'];
    }

    private function setEnvironment(string $key, string $value): void
    {
        if (!array_key_exists($key, $this->environment)) {
            $this->environment[$key] = getenv($key);
        }

        putenv($key . '=' . $value);
    }

    private function serviceWithClient(IdentityProviderClient $client): IamAuthorizationReadService
    {
        $service = new IamAuthorizationReadService();
        $property = new \ReflectionProperty(IamAuthorizationReadService::class, 'identityProviderClient');
        $property->setValue($service, $client);

        return $service;
    }

    /** @return array<int, array<string, mixed>> */
    private function captureAuthzLogs(callable $callback): array
    {
        $logger = Yii::getLogger();
        $offset = count($logger->messages);
        $callback();

        $entries = array_slice($logger->messages, $offset);
        $payloads = [];
        foreach ($entries as $entry) {
            if (($entry[2] ?? null) !== 'identity.iamAuthzRead' || !is_array($entry[0] ?? null)) {
                continue;
            }

            $payloads[] = $entry[0];
        }

        return $payloads;
    }
}
