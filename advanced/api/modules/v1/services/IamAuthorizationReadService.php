<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

class IamAuthorizationReadService extends Component
{
    private ?IdentityProviderClient $identityProviderClient = null;

    public function decide(
        object $user,
        string $permission,
        bool $legacyAllowed,
        string $resourceType = 'api',
        ?string $requestKey = null
    ): bool {
        if (!$this->routeIntegrationEnabled()) {
            return $legacyAllowed;
        }

        try {
            $subject = ['legacyUserId' => (int)$user->id];
            if (isset($user->username) && trim((string)$user->username) !== '') {
                $subject['username'] = (string)$user->username;
            }
            $payload = [
                'permission' => $permission,
                'resourceType' => $this->resourceType($resourceType),
                'subject' => $subject,
                'legacyDecision' => $legacyAllowed ? 'allow' : 'deny',
                'legacyPolicyVersion' => $this->legacyPolicyVersion(),
            ];
            if ($requestKey !== null && trim($requestKey) !== '') {
                $payload['requestKey'] = $requestKey;
            }
            $result = $this->identityProviderClient()->iamAuthzResolve($payload);
        } catch (\Throwable $exception) {
            $this->logUnavailable('IDENTITY_AUTHZ_TRANSPORT_FAILED');
            return $this->fallbackEnabled() ? $legacyAllowed : false;
        }

        if (!is_array($result)) {
            $this->logUnavailable('IDENTITY_AUTHZ_RESPONSE_MISSING');
            return $this->fallbackEnabled() ? $legacyAllowed : false;
        }

        $decision = $result['outcome']['decision'] ?? null;
        if (!in_array($decision, ['allow', 'deny'], true)) {
            $this->logUnavailable('IDENTITY_AUTHZ_DECISION_INVALID');
            return $this->fallbackEnabled() ? $legacyAllowed : false;
        }

        $responseSource = $result['outcome']['responseSource'] ?? null;
        $severity = $result['evidence']['severity'] ?? null;
        if (!in_array($responseSource, ['legacy', 'identity', 'legacy-fallback', 'fail-closed'], true)
            || !in_array($severity, ['none', 'p0', 'p1', 'info'], true)) {
            $this->logUnavailable('IDENTITY_AUTHZ_EVIDENCE_INVALID');
            return $this->fallbackEnabled() ? $legacyAllowed : false;
        }

        $this->logDecision($result);
        if ($severity === 'p0'
            || $responseSource === 'fail-closed'
            || (bool)($result['outcome']['failClosed'] ?? false)) {
            return false;
        }

        return $decision === 'allow';
    }

    protected function identityProviderClient(): IdentityProviderClient
    {
        if ($this->identityProviderClient === null) {
            $this->identityProviderClient = new IdentityProviderClient();
        }

        return $this->identityProviderClient;
    }

    public function routeIntegrationEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_IAM_AUTHZ_ROUTE_INTEGRATION_ENABLED', false);
    }

    private function fallbackEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_IAM_AUTHZ_FALLBACK_ENABLED', true);
    }

    private function legacyPolicyVersion(): string
    {
        return $this->stringConfig('IDENTITY_IAM_AUTHZ_LEGACY_POLICY_VERSION') ?? 'legacy-rbac-v1';
    }

    private function resourceType(string $resourceType): string
    {
        return in_array($resourceType, ['api', 'route', 'plugin'], true) ? $resourceType : 'api';
    }

    private function logDecision(array $result): void
    {
        $selection = is_array($result['selection'] ?? null) ? $result['selection'] : [];
        $outcome = is_array($result['outcome'] ?? null) ? $result['outcome'] : [];
        $evidence = is_array($result['evidence'] ?? null) ? $result['evidence'] : [];

        Yii::info([
            'event' => 'authorization.route-decision',
            'subjectHash' => $this->safeHash($selection['subjectHash'] ?? null),
            'permissionHash' => $this->safeHash($evidence['permissionHash'] ?? null),
            'requestKeyHash' => $this->safeHash($evidence['requestKeyHash'] ?? null),
            'configuredMode' => $this->safeEnum($selection['configuredMode'] ?? null, ['legacy', 'shadow', 'identity-primary']),
            'rolloutMode' => $this->safeEnum($selection['rolloutMode'] ?? null, ['off', 'allowlist', 'percentage', 'full']),
            'selectedForIdentityPrimary' => (bool)($selection['selectedForIdentityPrimary'] ?? false),
            'responseSource' => $this->safeEnum($outcome['responseSource'] ?? null, ['legacy', 'identity', 'legacy-fallback', 'fail-closed']),
            'decision' => $this->safeEnum($outcome['decision'] ?? null, ['allow', 'deny']),
            'fallbackUsed' => (bool)($outcome['fallbackUsed'] ?? false),
            'failClosed' => (bool)($outcome['failClosed'] ?? false),
            'severity' => $this->safeEnum($evidence['severity'] ?? null, ['none', 'p0', 'p1', 'info']),
            'classification' => $this->safeEnum($evidence['classification'] ?? null, [
                'not_compared',
                'match',
                'legacy_deny_identity_allow',
                'legacy_allow_identity_deny',
                'identity_read_error',
                'identity_decision_missing',
                'identity_policy_version_missing',
            ]),
        ], 'identity.iamAuthzRead');
    }

    private function logUnavailable(string $errorCode): void
    {
        Yii::warning([
            'event' => 'authorization.route-decision-unavailable',
            'errorCode' => $errorCode,
            'fallbackEnabled' => $this->fallbackEnabled(),
        ], 'identity.iamAuthzRead');
    }

    private function safeHash($value): ?string
    {
        return is_string($value) && preg_match('/^[a-f0-9]{16}$/', $value) === 1 ? $value : null;
    }

    private function safeEnum($value, array $allowed): ?string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : null;
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
        $value = $this->stringConfig($key);
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
}
