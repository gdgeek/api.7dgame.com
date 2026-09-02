<?php

namespace api\modules\v1\services;

final class Task51StageBSettings
{
    public const CLAIM_ORIGIN = 'https://d.xrugc.com';
    public const COORDINATOR_PUBLIC_ORIGIN = 'https://api.xrteeth.com';
    public const COORDINATOR_PUBLIC_HOST = 'api.xrteeth.com';

    public function __construct(private readonly ?string $releaseRoot = null)
    {
    }

    public function enabled(): bool
    {
        return in_array(strtolower(trim((string)getenv('TASK51_STAGE_B_COORDINATOR_ENABLED'))), [
            '1', 'true', 'yes', 'on',
        ], true);
    }

    public function internalToken(): ?string
    {
        $value = $this->nonEmptyEnvironmentValue('TASK51_STAGE_B_INTERNAL_TOKEN');
        return is_string($value) && $this->isExact256BitBase64Url($value)
            ? $value
            : null;
    }

    public function serverPublishSha(): ?string
    {
        $value = $this->nonEmptyEnvironmentValue('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA');
        $embedded = $this->embeddedServerPublishSha();
        return is_string($value)
            && preg_match('/^[a-f0-9]{40}$/D', $value) === 1
            && is_string($embedded)
            && hash_equals($embedded, $value)
            ? $value
            : null;
    }

    public function coordinatorPublicOrigin(): ?string
    {
        $value = $this->nonEmptyEnvironmentValue('TASK51_STAGE_B_COORDINATOR_PUBLIC_ORIGIN');
        return $value === self::COORDINATOR_PUBLIC_ORIGIN ? $value : null;
    }

    /** Public Host is preserved by the trusted edge and compared literally. */
    public function isCanonicalRequestHost(?string $host): bool
    {
        return is_string($host) && hash_equals(self::COORDINATOR_PUBLIC_HOST, $host);
    }

    public function isProductionRuntime(): bool
    {
        return defined('YII_DEBUG')
            && YII_DEBUG === false
            && defined('YII_ENV')
            && YII_ENV === 'prod';
    }

    public function isReady(): bool
    {
        return $this->enabled()
            && $this->internalToken() !== null
            && $this->coordinatorPublicOrigin() !== null
            && $this->serverPublishSha() !== null
            && $this->isProductionRuntime();
    }

    private function nonEmptyEnvironmentValue(string $name): ?string
    {
        $value = getenv($name);
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    /** The release workflow writes this immutable identity before building the image. */
    private function embeddedServerPublishSha(): ?string
    {
        $path = ($this->releaseRoot ?? dirname(__DIR__, 4)) . '/GIT_COMMIT';
        if (is_link($path) || !is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path, false, null, 0, 42);
        if (!is_string($raw) || preg_match('/^[a-f0-9]{40}\n?$/D', $raw) !== 1) {
            return null;
        }

        return substr($raw, 0, 40);
    }

    private function isExact256BitBase64Url(#[\SensitiveParameter] string $value): bool
    {
        if (preg_match('/^[A-Za-z0-9_-]{43}$/D', $value) !== 1) {
            return false;
        }
        $decoded = base64_decode(strtr($value, '-_', '+/') . '=', true);
        if (!is_string($decoded) || strlen($decoded) !== 32) {
            return false;
        }
        $canonical = rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=');
        return hash_equals($value, $canonical);
    }
}
