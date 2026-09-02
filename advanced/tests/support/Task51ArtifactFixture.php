<?php

namespace tests\support;

use api\modules\v1\services\Task51CanonicalArtifact;
use DateTimeImmutable;

final class Task51ArtifactFixture
{
    public const CAPABILITY = 'Y2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2M';
    public const SERVER_SHA = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    public const MATRIX_EVIDENCE_SHA256 = '8888888888888888888888888888888888888888888888888888888888888888';
    public const MATRIX_SUBJECT_DIGEST = '9999999999999999999999999999999999999999999999999999999999999999';

    /** @return array<string, mixed> */
    public static function stageB(
        ?DateTimeImmutable $issuedAt = null,
        ?DateTimeImmutable $expiresAt = null,
        string $executionId = 'task51-stage-b-20260828-test'
    ): array {
        return [
            'approvalRef' => 'WP3-TASK51-MEMORY-RUNNER-STAGE-B-20260828',
            'authorizedControlPostCount' => 1,
            'authorizedLogicalGetCount' => 56,
            'authorizedLoginCount' => 4,
            'authorizedLogoutCount' => 4,
            'claimCapabilitySha256' => hash('sha256', self::CAPABILITY),
            'coordinatorOrigin' => Task51CanonicalArtifact::COORDINATOR_ORIGIN,
            'coordinatorServerPublishSha' => self::SERVER_SHA,
            'currentWindowOnly' => true,
            'executionId' => $executionId,
            'expiresAt' => $expiresAt === null
                ? '2026-08-28T08:59:00.000Z'
                : Task51CanonicalArtifact::formatTimestamp($expiresAt),
            'issuedAt' => $issuedAt === null
                ? '2026-08-28T07:59:00.000Z'
                : Task51CanonicalArtifact::formatTimestamp($issuedAt),
            'oneShot' => true,
            'productionDirectMatrixAuthorizedCellCount' => 256,
            'productionDirectMatrixEvidenceRef' => 'reports/task51-production-direct-matrix.json',
            'productionDirectMatrixSchema' => Task51CanonicalArtifact::PRODUCTION_DIRECT_MATRIX_SCHEMA,
            'productionDirectMatrixSubjectDigest' => self::MATRIX_SUBJECT_DIGEST,
            'protocol' => Task51CanonicalArtifact::PROTOCOL,
            'schema' => Task51CanonicalArtifact::STAGE_B_SCHEMA,
            'stageAApprovalRef' => 'WP3-REL-TASK51-MEMORY-RUNNER-STAGE-A-20260828',
            'stageACoordinatorServerReleaseEvidenceSha256' => str_repeat('e', 64),
            'stageANetworkAttestorReleaseEvidenceSha256' => str_repeat('f', 64),
            'stageAReleaseEvidenceSha256' => str_repeat('b', 64),
            'status' => 'APPROVED',
        ];
    }

    /** @return array<string, mixed> */
    public static function runnerExport(
        string $rawStageB,
        ?DateTimeImmutable $exportedAt = null
    ): array {
        $stageB = Task51CanonicalArtifact::parseStageB($rawStageB);
        return [
            'approvalRef' => $stageB['approvalRef'],
            'executionId' => $stageB['executionId'],
            'exportedAt' => $exportedAt === null
                ? '2026-08-28T08:20:00.000Z'
                : Task51CanonicalArtifact::formatTimestamp($exportedAt),
            'globalConsumptionEvidenceRef' => null,
            'globalConsumptionEvidenceSha256' => null,
            'globalExactOneProved' => false,
            'productionDirectMatrixEvidenceRef' => $stageB['productionDirectMatrixEvidenceRef'],
            'productionDirectMatrixEvidenceSha256' => self::MATRIX_EVIDENCE_SHA256,
            'productionDirectMatrixSubjectDigest' => $stageB['productionDirectMatrixSubjectDigest'],
            'runnerFragmentEvidenceRef' => 'reports/task51-fragment.json',
            'runnerFragmentEvidenceSha256' => str_repeat('d', 64),
            'runnerResultEvidenceRef' => 'reports/task51-result.json',
            'runnerResultEvidenceSha256' => str_repeat('e', 64),
            'schema' => Task51CanonicalArtifact::RUNNER_EXPORT_SCHEMA,
            'stageBExecutionEvidenceRef' => 'reports/task51-stage-b.json',
            'stageBExecutionEvidenceSha256' => hash('sha256', $rawStageB),
            'stageBNetworkReceiptEvidenceRef' => 'reports/task51-network.json',
            'stageBNetworkReceiptEvidenceSha256' => str_repeat('c', 64),
            'status' => 'EVIDENCE_BOUND',
        ];
    }
}
