<?php

namespace api\modules\v1\services;

use DateTimeImmutable;
use DateTimeZone;
use JsonException;

/** Strict byte-level codecs for Task 5.1 control-plane artifacts. */
final class Task51CanonicalArtifact
{
    public const STAGE_B_SCHEMA = 'wp3-task51-stage-b-execution-approval-v3';
    public const CLAIM_RECEIPT_SCHEMA = 'wp3-task51-stage-b-global-claim-receipt-v1';
    public const RUNNER_FRAGMENT_SCHEMA = 'wp3-task51-runner-fragment-v3';
    public const RUNNER_RESULT_SCHEMA = 'wp3-task51-stage-b-runner-result-v3';
    public const RUNNER_EXPORT_SCHEMA = 'wp3-task51-stage-b-runner-export-receipt-v3';
    public const PRODUCTION_DIRECT_MATRIX_SCHEMA = 'wp3-task51-production-direct-matrix-v1';
    public const CONSUMPTION_RECEIPT_SCHEMA = 'wp3-task51-stage-b-global-consumption-receipt-v1';
    public const PROTOCOL = 'wp3-task51-memory-runner-v1';
    public const COORDINATOR_ORIGIN = 'https://api.xrteeth.com';
    public const MAX_STAGE_B_BYTES = 16 * 1024;
    public const MAX_CLAIM_RECEIPT_BYTES = 8 * 1024;
    public const MAX_RUNNER_EXPORT_BYTES = 16 * 1024;
    public const MAX_CONSUMPTION_RECEIPT_BYTES = 8 * 1024;

    private const STAGE_B_KEYS = [
        'approvalRef',
        'authorizedControlPostCount',
        'authorizedLogicalGetCount',
        'authorizedLoginCount',
        'authorizedLogoutCount',
        'claimCapabilitySha256',
        'coordinatorOrigin',
        'coordinatorServerPublishSha',
        'currentWindowOnly',
        'executionId',
        'expiresAt',
        'issuedAt',
        'oneShot',
        'productionDirectMatrixAuthorizedCellCount',
        'productionDirectMatrixEvidenceRef',
        'productionDirectMatrixSchema',
        'productionDirectMatrixSubjectDigest',
        'protocol',
        'schema',
        'stageAApprovalRef',
        'stageACoordinatorServerReleaseEvidenceSha256',
        'stageANetworkAttestorReleaseEvidenceSha256',
        'stageAReleaseEvidenceSha256',
        'status',
    ];

    private const RUNNER_EXPORT_KEYS = [
        'approvalRef',
        'executionId',
        'exportedAt',
        'globalConsumptionEvidenceRef',
        'globalConsumptionEvidenceSha256',
        'globalExactOneProved',
        'productionDirectMatrixEvidenceRef',
        'productionDirectMatrixEvidenceSha256',
        'productionDirectMatrixSubjectDigest',
        'runnerFragmentEvidenceRef',
        'runnerFragmentEvidenceSha256',
        'runnerResultEvidenceRef',
        'runnerResultEvidenceSha256',
        'schema',
        'stageBExecutionEvidenceRef',
        'stageBExecutionEvidenceSha256',
        'stageBNetworkReceiptEvidenceRef',
        'stageBNetworkReceiptEvidenceSha256',
        'status',
    ];

    /** @return array<string, mixed> */
    public static function parseStageB(#[\SensitiveParameter] string $raw): array
    {
        $value = self::parseCanonical($raw, self::MAX_STAGE_B_BYTES, self::STAGE_B_KEYS);
        self::assert($value['schema'] === self::STAGE_B_SCHEMA);
        self::assert($value['protocol'] === self::PROTOCOL);
        self::assert(is_string($value['approvalRef']) && strlen($value['approvalRef']) <= 191);
        self::assert(is_string($value['stageAApprovalRef']) && strlen($value['stageAApprovalRef']) <= 191);
        self::assert(self::matches($value['approvalRef'], '/^WP3-TASK51-[A-Z0-9-]*MEMORY-RUNNER[A-Z0-9-]*STAGE-B-[0-9]{8}$/D'));
        self::assert(self::matches($value['stageAApprovalRef'], '/^WP3-REL-TASK51-[A-Z0-9-]*MEMORY-RUNNER[A-Z0-9-]*STAGE-A-[0-9]{8}$/D'));
        self::assert(self::matches($value['executionId'], '/^task51-stage-b-[a-z0-9-]{8,96}$/D'));
        self::assert(self::isSha256($value['stageAReleaseEvidenceSha256']));
        self::assert(self::isSha256($value['stageACoordinatorServerReleaseEvidenceSha256']));
        self::assert(self::isSha256($value['stageANetworkAttestorReleaseEvidenceSha256']));
        self::assert(self::isSha256($value['claimCapabilitySha256']));
        self::assert($value['coordinatorOrigin'] === self::COORDINATOR_ORIGIN);
        self::assert(self::matches($value['coordinatorServerPublishSha'], '/^[a-f0-9]{40}$/D'));
        self::assert($value['status'] === 'APPROVED');
        self::assert($value['oneShot'] === true && $value['currentWindowOnly'] === true);
        self::assert($value['authorizedControlPostCount'] === 1);
        self::assert($value['authorizedLoginCount'] === 4);
        self::assert($value['authorizedLogoutCount'] === 4);
        self::assert($value['authorizedLogicalGetCount'] === 56);
        self::assert($value['productionDirectMatrixSchema'] === self::PRODUCTION_DIRECT_MATRIX_SCHEMA);
        self::assert(self::isEvidenceRef($value['productionDirectMatrixEvidenceRef']));
        self::assert($value['productionDirectMatrixAuthorizedCellCount'] === 256);
        self::assert(self::isSha256($value['productionDirectMatrixSubjectDigest']));
        $issuedAt = self::parseTimestamp($value['issuedAt']);
        $expiresAt = self::parseTimestamp($value['expiresAt']);
        self::assert($expiresAt > $issuedAt);

        return $value;
    }

    /** @return array<string, mixed> */
    public static function parseRunnerExport(#[\SensitiveParameter] string $raw): array
    {
        $value = self::parseCanonical($raw, self::MAX_RUNNER_EXPORT_BYTES, self::RUNNER_EXPORT_KEYS);
        self::assert($value['schema'] === self::RUNNER_EXPORT_SCHEMA);
        self::assert(is_string($value['approvalRef']) && strlen($value['approvalRef']) <= 191);
        self::assert(self::matches($value['approvalRef'], '/^WP3-TASK51-[A-Z0-9-]*MEMORY-RUNNER[A-Z0-9-]*STAGE-B-[0-9]{8}$/D'));
        self::assert(self::matches($value['executionId'], '/^task51-stage-b-[a-z0-9-]{8,96}$/D'));
        self::assert($value['status'] === 'EVIDENCE_BOUND');
        self::assert($value['globalExactOneProved'] === false);
        self::assert($value['globalConsumptionEvidenceRef'] === null);
        self::assert($value['globalConsumptionEvidenceSha256'] === null);
        foreach ([
            'stageBExecutionEvidenceSha256',
            'stageBNetworkReceiptEvidenceSha256',
            'runnerFragmentEvidenceSha256',
            'runnerResultEvidenceSha256',
            'productionDirectMatrixEvidenceSha256',
            'productionDirectMatrixSubjectDigest',
        ] as $key) {
            self::assert(self::isSha256($value[$key]));
        }
        foreach ([
            'stageBExecutionEvidenceRef',
            'stageBNetworkReceiptEvidenceRef',
            'runnerFragmentEvidenceRef',
            'runnerResultEvidenceRef',
            'productionDirectMatrixEvidenceRef',
        ] as $key) {
            self::assert(self::isEvidenceRef($value[$key]));
        }
        self::parseTimestamp($value['exportedAt']);

        return $value;
    }

    /** @param array<string, mixed> $value */
    public static function encode(#[\SensitiveParameter] array $value, int $maxBytes): string
    {
        $canonical = self::canonicalCopy($value);
        try {
            $raw = json_encode(
                $canonical,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
            ) . "\n";
        } catch (JsonException $exception) {
            throw new Task51ArtifactException('Task 5.1 artifact is not JSON encodable.', 0, $exception);
        }
        $raw = str_replace("\x7f", '\\u007f', $raw);
        self::assert(strlen($raw) <= $maxBytes);
        self::assert(!preg_match('/[^\x00-\x7f]/', $raw));

        return $raw;
    }

    /** @param array<string, mixed> $value @return array<string, mixed> */
    private static function canonicalCopy(#[\SensitiveParameter] array $value): array
    {
        ksort($value, SORT_STRING);
        foreach ($value as $key => $entry) {
            if (is_array($entry)) {
                $value[$key] = array_is_list($entry)
                    ? array_map(static fn(mixed $item): mixed => is_array($item) ? self::canonicalCopy($item) : $item, $entry)
                    : self::canonicalCopy($entry);
            }
            self::assert(!is_float($entry) || is_finite($entry));
            self::assert(is_null($entry) || is_scalar($entry) || is_array($entry));
        }

        return $value;
    }

    /** @param list<string> $keys @return array<string, mixed> */
    private static function parseCanonical(
        #[\SensitiveParameter] string $raw,
        int $maxBytes,
        array $keys
    ): array
    {
        self::assert($raw !== '' && strlen($raw) <= $maxBytes);
        self::assert(!preg_match('/[^\x00-\x7f]/', $raw));
        self::assert(str_ends_with($raw, "\n") && !str_ends_with($raw, "\n\n"));
        try {
            $value = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new Task51ArtifactException('Task 5.1 artifact is invalid JSON.', 0, $exception);
        }
        self::assert(is_array($value) && !array_is_list($value));
        $actualKeys = array_keys($value);
        sort($actualKeys, SORT_STRING);
        $expectedKeys = $keys;
        sort($expectedKeys, SORT_STRING);
        self::assert($actualKeys === $expectedKeys);
        self::assert(self::encode($value, $maxBytes) === $raw);

        return $value;
    }

    public static function parseTimestamp(mixed $value): DateTimeImmutable
    {
        self::assert(is_string($value));
        self::assert(preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d{3})?(Z|[+-](\d{2}):(\d{2}))$/D',
            $value,
            $parts
        ) === 1);
        self::assert(checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1]));
        self::assert((int)$parts[4] <= 23 && (int)$parts[5] <= 59 && (int)$parts[6] <= 59);
        if ($parts[7] !== 'Z') {
            self::assert((int)$parts[8] <= 14 && (int)$parts[9] <= 59);
            self::assert((int)$parts[8] < 14 || (int)$parts[9] === 0);
        }
        try {
            $parsed = new DateTimeImmutable($value);
        } catch (\Exception) {
            throw new Task51ArtifactException('Task 5.1 timestamp is invalid.');
        }

        return $parsed->setTimezone(new DateTimeZone('UTC'));
    }

    public static function formatTimestamp(DateTimeImmutable $value): string
    {
        return $value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z');
    }

    public static function sha256(#[\SensitiveParameter] string $raw): string
    {
        return hash('sha256', $raw);
    }

    public static function isEvidenceRef(mixed $value): bool
    {
        if (!is_string($value) || strlen($value) > 512
            || !self::matches($value, '#^reports/[A-Za-z0-9._/-]+\.json$#D')) {
            return false;
        }
        foreach (explode('/', $value) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return false;
            }
        }

        return true;
    }

    private static function isSha256(mixed $value): bool
    {
        return self::matches($value, '/^[a-f0-9]{64}$/D')
            && $value !== str_repeat('0', 64);
    }

    private static function matches(mixed $value, string $pattern): bool
    {
        return is_string($value) && preg_match($pattern, $value) === 1;
    }

    private static function assert(bool $condition): void
    {
        if (!$condition) {
            throw new Task51ArtifactException('Task 5.1 artifact contract rejected.');
        }
    }
}
