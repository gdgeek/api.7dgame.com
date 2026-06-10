<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use Yii;
use yii\base\Component;

class IamShadowCompareService extends Component
{
    private ?IdentityProviderClient $identityProviderClient = null;

    public function compareCurrentUserPayload(User $user, array $payload): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $this->guarded('user.info', function () use ($user, $payload): void {
            $iamUser = $this->identityProviderClient()->iamUserView((int)$user->id);
            if ($iamUser !== null) {
                $this->compareScalar('user.info.id', (string)$user->id, (string)($iamUser['legacyUserId'] ?? ''));
                $this->compareScalar('user.info.email', $payload['email'] ?? null, $iamUser['email'] ?? null, 'p2');
            }

            $iamRoles = $this->identityProviderClient()->iamRolesView((int)$user->id);
            if ($iamRoles !== null) {
                $this->compareSet(
                    'user.info.roles',
                    $payload['roles'] ?? [],
                    array_map(static fn(array $role): string => (string)($role['name'] ?? ''), $iamRoles['roles'] ?? []),
                    'p1'
                );
            }

            $iamOrganizations = $this->identityProviderClient()->iamOrganizationsView((int)$user->id);
            if ($iamOrganizations !== null) {
                $this->compareSet(
                    'user.info.organizations',
                    array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $payload['organizations'] ?? []),
                    array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $iamOrganizations['organizations'] ?? []),
                    'p1'
                );
            }
        });
    }

    public function comparePluginVerifyToken(User $user, array $roles, array $organizations, ?string $authorizationHeader): void
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
                return;
            }

            $this->compareScalar('plugin.verify-token.id', (string)$user->id, (string)($iamUser['uid'] ?? $iamUser['id'] ?? ''), 'p1');
            $this->compareSet('plugin.verify-token.roles', $roles, $iamUser['roles'] ?? [], 'p1');
            $this->compareSet(
                'plugin.verify-token.organizations',
                array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $organizations),
                array_map(static fn(array $organization): string => (string)($organization['id'] ?? ''), $iamUser['organizations'] ?? []),
                'p1'
            );
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
                return;
            }

            $this->compareSet(
                $context . '.roles',
                $roles,
                array_map(static fn(array $role): string => (string)($role['name'] ?? ''), $iamRoles['roles'] ?? []),
                'p1'
            );
        });
    }

    public function comparePermission(User $user, string $permission, bool $legacyAllowed): void
    {
        if (!$this->shouldCompare()) {
            return;
        }

        $this->guarded('permission.' . $permission, function () use ($user, $permission, $legacyAllowed): void {
            $iamPermissions = $this->identityProviderClient()->iamPermissionsView((int)$user->id);
            if ($iamPermissions === null) {
                return;
            }

            $names = array_map(static fn(array $item): string => (string)($item['name'] ?? ''), $iamPermissions['permissions'] ?? []);
            $iamAllowed = in_array($permission, $names, true);
            if ($legacyAllowed !== $iamAllowed) {
                $this->logMismatch('permission.' . $permission, 'p1', $legacyAllowed, $iamAllowed, [
                    'legacyUserId' => (int)$user->id,
                ]);
            }
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

    private function compareScalar(string $field, $legacyValue, $identityValue, string $severity = 'p1'): void
    {
        if ((string)$legacyValue === (string)$identityValue) {
            return;
        }

        $this->logMismatch($field, $severity, $legacyValue, $identityValue);
    }

    private function compareSet(string $field, array $legacyValues, array $identityValues, string $severity): void
    {
        $legacy = $this->normalizeSet($legacyValues);
        $identity = $this->normalizeSet($identityValues);
        if ($legacy === $identity) {
            return;
        }

        $this->logMismatch($field, $severity, $legacy, $identity, [
            'legacyCount' => count($legacy),
            'identityCount' => count($identity),
        ]);
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

        return hash('sha256', json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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
