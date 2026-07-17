<?php

namespace tests\unit\services;

use api\modules\v1\services\IamShadowCompareService;
use api\modules\v1\services\IdentityProviderClient;
use PHPUnit\Framework\TestCase;
use Yii;

final class IamShadowCompareServiceTest extends TestCase
{
    private const HASH_SALT = 'unit-shadow-compare-salt';

    /** @var array<string, string|false> */
    private array $environment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setEnvironment('IDENTITY_IAM_SHADOW_COMPARE', 'true');
        $this->setEnvironment('IDENTITY_IAM_SHADOW_COMPARE_HASH_SALT', self::HASH_SALT);
    }

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

    public function testUserInfoCompletionIsCorrelatedAndDoesNotExposeTheSubject(): void
    {
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamUserView(int $legacyUserId): ?array
            {
                return ['legacyUserId' => $legacyUserId, 'email' => 'compare@example.test'];
            }

            public function iamRolesView(int $legacyUserId): ?array
            {
                return ['roles' => [['name' => 'manager']]];
            }

            public function iamOrganizationsView(int $legacyUserId): ?array
            {
                return ['organizations' => [['id' => 'org-test']]];
            }
        });
        $user = (object)['id' => 42];

        $entries = $this->captureShadowLogs(function () use ($service, $user): void {
            $service->compareCurrentUserPayload($user, [
                'email' => 'compare@example.test',
                'roles' => ['manager'],
                'organizations' => [['id' => 'org-test']],
            ]);
        });

        $completion = $this->event($entries, 'comparison.completed');
        $this->assertSame('user.info', $completion['context']);
        $this->assertSame(4, $completion['comparisonCount']);
        $this->assertSame(0, $completion['mismatchCount']);
        $this->assertSame('match', $completion['outcome']);
        $this->assertSame(hash_hmac('sha256', 'subject:42', self::HASH_SALT), $completion['subjectHash']);
        $this->assertStringNotContainsString('compare@example.test', (string)json_encode($entries));
        $this->assertStringNotContainsString('legacyUserId', (string)json_encode($completion));
    }

    public function testDefaultLegacyModeDoesNotCallOrLogShadowCompare(): void
    {
        $this->setEnvironment('IDENTITY_IAM_SHADOW_COMPARE', 'false');
        $this->setEnvironment('IDENTITY_IAM_PROVIDER', 'legacy');
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamRolesView(int $legacyUserId): ?array
            {
                throw new \RuntimeException('Shadow client must not run in default legacy mode.');
            }
        });

        $entries = $this->captureShadowLogs(function () use ($service): void {
            $service->compareRolesByUserId(99, ['manager'], 'authorization');
        });

        $this->assertSame([], $entries);
    }

    public function testUnavailableIdentityViewCreatesIncompleteEvidenceInsteadOfCompletion(): void
    {
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamUserView(int $legacyUserId): ?array
            {
                return ['legacyUserId' => $legacyUserId, 'email' => null];
            }

            public function iamRolesView(int $legacyUserId): ?array
            {
                return ['roles' => []];
            }

            public function iamOrganizationsView(int $legacyUserId): ?array
            {
                return null;
            }
        });

        $entries = $this->captureShadowLogs(function () use ($service): void {
            $service->compareCurrentUserPayload((object)['id' => 43], []);
        });

        $incomplete = $this->event($entries, 'comparison.incomplete');
        $this->assertSame('user.info', $incomplete['context']);
        $this->assertSame(['organizations'], $incomplete['missingViews']);
        $this->assertSame('incomplete', $incomplete['outcome']);
        $this->assertFalse($this->containsEvent($entries, 'comparison.completed'));
    }

    public function testAuthorizationRoleReadProducesACompletionEvent(): void
    {
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamRolesView(int $legacyUserId): ?array
            {
                return ['roles' => [['name' => 'manager']]];
            }
        });

        $entries = $this->captureShadowLogs(function () use ($service): void {
            $service->compareRolesByUserId(46, ['manager'], 'authorization');
        });

        $completion = $this->event($entries, 'comparison.completed');
        $this->assertSame('authorization', $completion['context']);
        $this->assertSame(1, $completion['comparisonCount']);
        $this->assertSame('match', $completion['outcome']);
    }

    public function testPermissionMismatchUsesOnlyHashedSubjectAndPermissionEvidence(): void
    {
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamPermissionsView(int $legacyUserId): ?array
            {
                return ['permissions' => []];
            }
        });
        $user = (object)['id' => 44];

        $entries = $this->captureShadowLogs(function () use ($service, $user): void {
            $service->comparePermission($user, 'organization.bind-user', true);
        });

        $completion = $this->event($entries, 'comparison.completed');
        $mismatch = $this->firstPayloadWithoutEvent($entries);
        $this->assertSame('permission.check', $completion['context']);
        $this->assertSame(1, $completion['mismatchCount']);
        $this->assertSame('mismatch', $completion['outcome']);
        $this->assertArrayHasKey('permissionHash', $completion['metadata']);
        $this->assertArrayHasKey('subjectHash', $mismatch['metadata']);
        $this->assertArrayNotHasKey('legacyUserId', $mismatch['metadata']);
        $this->assertSame('permission.check', $mismatch['field']);
        $this->assertStringNotContainsString('organization.bind-user', (string)json_encode($entries));
    }

    public function testPluginCompletionDoesNotLogTheBearerToken(): void
    {
        $service = $this->serviceWithClient(new class extends IdentityProviderClient {
            public function iamPluginVerifyToken(string $token): ?array
            {
                return [
                    'user' => [
                        'uid' => 45,
                        'roles' => ['manager'],
                        'organizations' => [['id' => 'org-test']],
                    ],
                ];
            }
        });
        $user = (object)['id' => 45];
        $token = 'token-must-never-be-logged';

        $entries = $this->captureShadowLogs(function () use ($service, $user, $token): void {
            $service->comparePluginVerifyToken(
                $user,
                ['manager'],
                [['id' => 'org-test']],
                'Bearer ' . $token
            );
        });

        $completion = $this->event($entries, 'comparison.completed');
        $this->assertSame('plugin.verify-token', $completion['context']);
        $this->assertSame(3, $completion['comparisonCount']);
        $this->assertStringNotContainsString($token, (string)json_encode($entries));
    }

    private function setEnvironment(string $key, string $value): void
    {
        if (!array_key_exists($key, $this->environment)) {
            $this->environment[$key] = getenv($key);
        }

        putenv($key . '=' . $value);
    }

    private function serviceWithClient(IdentityProviderClient $client): IamShadowCompareService
    {
        $service = new IamShadowCompareService();
        $property = new \ReflectionProperty(IamShadowCompareService::class, 'identityProviderClient');
        $property->setValue($service, $client);

        return $service;
    }

    /** @return array<int, array<string, mixed>> */
    private function captureShadowLogs(callable $callback): array
    {
        $logger = Yii::getLogger();
        $offset = count($logger->messages);
        $callback();

        $entries = array_slice($logger->messages, $offset);
        $payloads = [];
        foreach ($entries as $entry) {
            if (($entry[2] ?? null) !== 'identity.iamShadowCompare' || !is_array($entry[0] ?? null)) {
                continue;
            }

            $payloads[] = $entry[0];
        }

        return $payloads;
    }

    /** @param array<int, array<string, mixed>> $entries */
    private function event(array $entries, string $event): array
    {
        foreach ($entries as $entry) {
            if (($entry['event'] ?? null) === $event) {
                return $entry;
            }
        }

        $this->fail('Expected shadow compare event was not logged: ' . $event);
    }

    /** @param array<int, array<string, mixed>> $entries */
    private function containsEvent(array $entries, string $event): bool
    {
        foreach ($entries as $entry) {
            if (($entry['event'] ?? null) === $event) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, array<string, mixed>> $entries */
    private function firstPayloadWithoutEvent(array $entries): array
    {
        foreach ($entries as $entry) {
            if (!isset($entry['event'])) {
                return $entry;
            }
        }

        $this->fail('Expected mismatch payload was not logged.');
    }
}
