<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

class IamShadowCompareService extends Component
{
    private ?IdentityProviderClient $identityProviderClient = null;

    public function compareCurrentUserPayload(object $user, array $payload): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $this->guarded('user.info', function () use ($user, $payload): void {
            $legacyUserId = (int)$user->id;
            $mismatchCount = 0;
            $missingViews = [];

            $iamUser = $this->identityProviderClient()->iamUserView((int)$user->id);
            if ($iamUser === null) {
                $missingViews[] = 'user';
            } else {
                $mismatchCount += (int)$this->compareScalar('user.info.id', (string)$user->id, (string)($iamUser['legacyUserId'] ?? ''));
                $mismatchCount += (int)$this->compareScalar('user.info.email', $payload['email'] ?? null, $iamUser['email'] ?? null, 'p2');
            }

            $iamRoles = $this->identityProviderClient()->iamRolesView((int)$user->id);
            if ($iamRoles === null) {
                $missingViews[] = 'roles';
            } else {
                $mismatchCount += (int)$this->compareSet(
                    'user.info.roles',
                    $payload['roles'] ?? [],
                    array_map(static fn(array $role): string => (string)($role['name'] ?? ''), $iamRoles['roles'] ?? []),
                    'p1'
                );
            }

            $iamOrganizations = $this->identityProviderClient()->iamOrganizationsView((int)$user->id);
            if ($iamOrganizations === null) {
                $missingViews[] = 'organizations';
            } else {
                $mismatchCount += (int)$this->compareSet(
                    'user.info.organizations',
                    array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $payload['organizations'] ?? []),
                    array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $iamOrganizations['organizations'] ?? []),
                    'p1'
                );
            }

            if ($missingViews !== []) {
                $this->logIncompleteComparison('user.info', $legacyUserId, $missingViews, 4);
                return;
            }

            $this->logCompletedComparison('user.info', $legacyUserId, 4, $mismatchCount);
        });
    }

    public function comparePluginVerifyToken(object $user, array $roles, array $organizations, ?string $authorizationHeader): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $token = $this->bearerToken($authorizationHeader);
        if ($token === null) {
            return;
        }

        $this->guarded('plugin.verify-token', function () use ($user, $roles, $organizations, $token): void {
            $iamPlugin = $this->identityProviderClient()->iamPluginVerifyToken($token);
            $iamUser = is_array($iamPlugin) && isset($iamPlugin['user']) && is_array($iamPlugin['user']) ? $iamPlugin['user'] : null;
            if ($iamUser === null) {
                $this->logIncompleteComparison('plugin.verify-token', (int)$user->id, ['plugin']);
                return;
            }

            $mismatchCount = 0;
            $mismatchCount += (int)$this->compareScalar('plugin.verify-token.id', (string)$user->id, (string)($iamUser['uid'] ?? $iamUser['id'] ?? ''), 'p1');
            $mismatchCount += (int)$this->compareSet('plugin.verify-token.roles', $roles, $iamUser['roles'] ?? [], 'p1');
            $mismatchCount += (int)$this->compareSet(
                'plugin.verify-token.organizations',
                array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $organizations),
                array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $iamUser['organizations'] ?? []),
                'p1'
            );

            $this->logCompletedComparison('plugin.verify-token', (int)$user->id, 3, $mismatchCount);
        });
    }

    public function compareRolesByUserId(int $userId, array $roles, string $context): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $this->guarded($context, function () use ($userId, $roles, $context): void {
            $iamRoles = $this->identityProviderClient()->iamRolesView($userId);
            if ($iamRoles === null) {
                $this->logIncompleteComparison($context, $userId, ['roles']);
                return;
            }

            $mismatchCount = (int)$this->compareSet(
                $context . '.roles',
                $roles,
                array_map(static fn(array $role): string => (string)($role['name'] ?? ''), $iamRoles['roles'] ?? []),
                'p1'
            );

            $this->logCompletedComparison($context, $userId, 1, $mismatchCount);
        });
    }

    public function comparePermission(object $user, string $permission, bool $legacyAllowed): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $this->guarded('permission.check', function () use ($user, $permission, $legacyAllowed): void {
            $legacyUserId = (int)$user->id;
            $iamPermissions = $this->identityProviderClient()->iamPermissionsView($legacyUserId);
            if ($iamPermissions === null) {
                $this->logIncompleteComparison('permission.check', $legacyUserId, ['permissions']);
                return;
            }

            $names = array_map(static fn(array $item): string => (string)($item['name'] ?? ''), $iamPermissions['permissions'] ?? []);
            $iamAllowed = in_array($permission, $names, true);
            $mismatchCount = (int)($legacyAllowed !== $iamAllowed);
            if ($mismatchCount === 1) {
                $this->logMismatch('permission.check', 'p1', $legacyAllowed, $iamAllowed, [
                    'subjectHash' => $this->subjectHash($legacyUserId),
                    'permissionHash' => $this->hashValue($permission),
                ]);
            }

            $this->logCompletedComparison('permission.check', $legacyUserId, 1, $mismatchCount, [
                'permissionHash' => $this->hashValue($permission),
            ]);
        });
    }

    public function identityProviderClient(): IdentityProviderClient
    {
        if ($this->identityProviderClient === null) {
            $this->identityProviderClient = new IdentityProviderClient();
        }

        return $this->identityProviderClient;
    }

    private function shouldCompare(): bool
    {
        return $this->provider() === 'identity-shadow'
            || $this->boolConfig('IDENTITY_IAM_SHADOW_COMPARE', false);
    }

    private function provider(): string
    {
        $provider = strtolower((string)($this->stringConfig('IDENTITY_IAM_PROVIDER') ?? 'legacy'));

        return in_array($provider, ['identity', 'identity-shadow'], true) ? $provider : 'legacy';
    }

    private function guarded(string $context, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            Yii::warning('Identity IAM shadow compare failed for ' . $context . ': ' . $exception->getMessage(), 'identity.iamShadowCompare');
            if (!$this->fallbackEnabled()) {
                throw $exception;
            }
        }
    }

    private function compareScalar(string $field, $legacyValue, $identityValue, string $severity = 'p1'): bool
    {
        if ((string)$legacyValue === (string)$identityValue) {
            return false;
        }

        $this->logMismatch($field, $severity, $legacyValue, $identityValue);
        return true;
    }

    private function compareSet(string $field, array $legacyValues, array $identityValues, string $severity): bool
    {
        $legacy = $this->normalizeSet($legacyValues);
        $identity = $this->normalizeSet($identityValues);
        if ($legacy === $identity) {
            return false;
        }

        $this->logMismatch($field, $severity, $legacy, $identity, [
            'legacyCount' => count($legacy),
            'identityCount' => count($identity),
        ]);
        return true;
    }

    private function logMismatch(string $field, string $severity, $legacyValue, $identityValue, array $metadata = []): void
    {
        Yii::warning([
            'field' => $field,
            'severity' => $severity,
            'legacyHash' => $this->hashValue($legacyValue),
            'identityHash' => $this->hashValue($identityValue),
            'metadata' => $metadata,
        ], 'identity.iamShadowCompare');
    }

    private function logCompletedComparison(
        string $context,
        int $legacyUserId,
        int $comparisonCount,
        int $mismatchCount,
        array $metadata = []
    ): void {
        $subjectHash = $this->subjectHash($legacyUserId);
        if ($subjectHash === null) {
            $this->logIncompleteComparison($context, $legacyUserId, ['correlation-secret'], $comparisonCount);
            return;
        }

        Yii::info([
            'event' => 'comparison.completed',
            'context' => $context,
            'subjectHash' => $subjectHash,
            'comparisonCount' => $comparisonCount,
            'mismatchCount' => $mismatchCount,
            'outcome' => $mismatchCount === 0 ? 'match' : 'mismatch',
            'metadata' => $metadata,
        ], 'identity.iamShadowCompare');
    }

    private function logIncompleteComparison(
        string $context,
        int $legacyUserId,
        array $missingViews,
        int $comparisonCount = 0
    ): void {
        $subjectHash = $this->subjectHash($legacyUserId);
        Yii::info([
            'event' => 'comparison.incomplete',
            'context' => $context,
            'subjectHash' => $subjectHash,
            'comparisonCount' => $comparisonCount,
            'missingViews' => array_values($missingViews),
            'outcome' => 'incomplete',
        ], 'identity.iamShadowCompare');
    }

    private function normalizeSet(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            $text = trim((string)$value);
            if ($text !== '') {
                $normalized[] = $text;
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized, SORT_STRING);

        return $normalized;
    }

    private function hashValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $payload = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $secret = $this->correlationSecret();
        if ($secret === null) {
            return hash('sha256', $payload);
        }

        return hash_hmac('sha256', $payload, $secret);
    }

    private function subjectHash(int $legacyUserId): ?string
    {
        $secret = $this->correlationSecret();
        if ($secret === null) {
            return null;
        }

        return hash_hmac('sha256', 'subject:' . $legacyUserId, $secret);
    }

    private function correlationSecret(): ?string
    {
        return $this->stringConfig('IDENTITY_IAM_SHADOW_COMPARE_HASH_SALT')
            ?? $this->stringConfig('IDENTITY_IAM_INTERNAL_API_TOKEN')
            ?? $this->stringConfig('IDENTITY_INTERNAL_API_TOKEN');
    }

    private function bearerToken(?string $authorizationHeader): ?string
    {
        if ($authorizationHeader === null || stripos($authorizationHeader, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($authorizationHeader, 7));
        return $token === '' ? null : $token;
    }

    private function fallbackEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_IAM_FALLBACK', true);
    }

    private function stringConfig(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityAuth'][$key])) {
            $value = Yii::$app->params['identityAuth'][$key];
        }
        if ($value === false || $value === null) {
            return null;
        }

        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function boolConfig(string $key, bool $default): bool
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityAuth'][$key])) {
            $value = Yii::$app->params['identityAuth'][$key];
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
    }
}
