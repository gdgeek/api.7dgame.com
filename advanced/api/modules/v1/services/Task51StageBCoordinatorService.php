<?php

namespace api\modules\v1\services;

use DateInterval;
use DateTimeImmutable;

final class Task51StageBCoordinatorService
{
    private const ISSUED = 'ISSUED';
    private const CLAIMED = 'CLAIMED';
    private const CONSUMED = 'CONSUMED';
    private const MIN_WINDOW_SECONDS = 35 * 60;
    private const MAX_WINDOW_SECONDS = 2 * 60 * 60;
    private const MIN_CLAIM_REMAINING_SECONDS = 30 * 60 + 15;

    public function __construct(
        private readonly Task51StageBRepositoryInterface $repository,
        private readonly string $serverPublishSha
    ) {
        if (preg_match('/^[a-f0-9]{40}$/D', $serverPublishSha) !== 1) {
            throw new Task51CoordinatorException(
                Task51CoordinatorException::UNAVAILABLE,
                'Coordinator publish identity is unavailable.'
            );
        }
    }

    /** @return array<string, mixed> safe metadata only */
    public function issue(
        #[\SensitiveParameter] string $rawStageB,
        #[\SensitiveParameter] string $claimCapability
    ): array
    {
        $stageB = Task51CanonicalArtifact::parseStageB($rawStageB);
        $this->assertDeploymentBinding($stageB);
        $capabilityHash = $this->capabilityHash($claimCapability);
        if (!hash_equals($stageB['claimCapabilitySha256'], $capabilityHash)) {
            throw $this->invalid('Claim capability does not match Stage B.');
        }
        $stageBHash = Task51CanonicalArtifact::sha256($rawStageB);

        return $this->repository->transaction(function () use ($stageB, $stageBHash, $capabilityHash): array {
            $now = $this->repository->now();
            [$issuedAt, $expiresAt] = $this->assertIssueWindow($stageB, $now);
            $row = [
                'execution_id' => $stageB['executionId'],
                'approval_ref' => $stageB['approvalRef'],
                'stage_b_sha256' => $stageBHash,
                'claim_capability_sha256' => $capabilityHash,
                'coordinator_origin' => $stageB['coordinatorOrigin'],
                'coordinator_server_publish_sha' => $stageB['coordinatorServerPublishSha'],
                'state' => self::ISSUED,
                'state_version' => 0,
                'claim_count' => 0,
                'consumption_count' => 0,
                'issued_at' => $this->dbTimestamp($issuedAt),
                'expires_at' => $this->dbTimestamp($expiresAt),
                'created_at' => $this->dbTimestamp($now),
                'updated_at' => $this->dbTimestamp($now),
                'production_direct_matrix_evidence_ref' =>
                    $stageB['productionDirectMatrixEvidenceRef'],
                'production_direct_matrix_subject_digest' =>
                    $stageB['productionDirectMatrixSubjectDigest'],
            ];
            if (!$this->repository->insertExecution($row)) {
                $existing = $this->repository->findExecution($stageB['executionId'], true);
                if (!$this->sameIssue($existing, $row)) {
                    throw $this->conflict('Execution, approval, or Stage B hash is already bound.');
                }
                return $this->safeIssueMetadata($existing);
            }
            // A unique-key insert can wait for another transaction. Re-sample
            // the DB clock after it succeeds so lock contention cannot issue
            // an already-expired execution.
            $insertedAt = $this->repository->now();
            $this->assertIssueWindow($stageB, $insertedAt);
            $insertedTimestamp = $this->dbTimestamp($insertedAt);
            // MySQL reports affected_rows=0 for an UPDATE that writes the
            // values already stored by INSERT. In the common same-millisecond
            // case there is nothing to refresh, so do not misread that no-op
            // as a failed CAS. A later clock sample still requires the CAS.
            if ($insertedTimestamp !== $row['created_at']) {
                if (!$this->repository->compareAndSwapState($stageB['executionId'], self::ISSUED, 0, [
                    'created_at' => $insertedTimestamp,
                    'updated_at' => $insertedTimestamp,
                ])) {
                    throw $this->conflict('Issued Stage B lost its authoritative row before commit.');
                }
                $row['created_at'] = $insertedTimestamp;
                $row['updated_at'] = $insertedTimestamp;
            }
            $this->repository->appendTransition([
                'execution_id' => $stageB['executionId'],
                'ordinal' => 0,
                'from_state' => null,
                'to_state' => self::ISSUED,
                'state_version' => 0,
                'evidence_sha256' => $stageBHash,
                'occurred_at' => $this->dbTimestamp($insertedAt),
            ]);

            return $this->safeIssueMetadata($row);
        });
    }

    /** Returns canonical safe receipt bytes. */
    public function claim(
        #[\SensitiveParameter] string $rawStageB,
        #[\SensitiveParameter] string $claimCapability
    ): string
    {
        $stageB = Task51CanonicalArtifact::parseStageB($rawStageB);
        $this->assertDeploymentBinding($stageB);
        $capabilityHash = $this->capabilityHash($claimCapability);
        if (!hash_equals($stageB['claimCapabilitySha256'], $capabilityHash)) {
            throw $this->invalid('Claim capability does not match Stage B.');
        }
        $stageBHash = Task51CanonicalArtifact::sha256($rawStageB);

        return $this->repository->transaction(function () use ($stageB, $stageBHash, $capabilityHash): string {
            $row = $this->repository->findExecution($stageB['executionId'], true);
            $this->assertStoredBinding($row, $stageB, $stageBHash, $capabilityHash);
            if (($row['state'] ?? null) !== self::ISSUED || (int)($row['state_version'] ?? -1) !== 0) {
                throw $this->conflict('Stage B claim has already been used.');
            }
            // Sample the authoritative clock only after acquiring the row
            // lock. Otherwise lock contention could carry a stale timestamp
            // beyond expiry while still allowing the transition.
            $now = $this->repository->now();
            $expiresAt = Task51CanonicalArtifact::parseTimestamp($stageB['expiresAt']);
            if ($now >= $expiresAt
                || $expiresAt < $now->add(new DateInterval('PT' . self::MIN_CLAIM_REMAINING_SECONDS . 'S'))) {
                throw $this->expired('Stage B is expired or has insufficient execution time remaining.');
            }
            $receipt = Task51CanonicalArtifact::encode([
                'approvalRef' => $stageB['approvalRef'],
                'claimCount' => 1,
                'claimedAt' => Task51CanonicalArtifact::formatTimestamp($now),
                'coordinatorOrigin' => $stageB['coordinatorOrigin'],
                'coordinatorServerPublishSha' => $stageB['coordinatorServerPublishSha'],
                'executionId' => $stageB['executionId'],
                'expiresAt' => $stageB['expiresAt'],
                'globalExactOneClaimed' => true,
                'schema' => Task51CanonicalArtifact::CLAIM_RECEIPT_SCHEMA,
                'stageBExecutionEvidenceSha256' => $stageBHash,
                'state' => self::CLAIMED,
            ], Task51CanonicalArtifact::MAX_CLAIM_RECEIPT_BYTES);
            if (!$this->repository->compareAndSwapState($stageB['executionId'], self::ISSUED, 0, [
                'state' => self::CLAIMED,
                'state_version' => 1,
                'claim_count' => 1,
                'claimed_at' => $this->dbTimestamp($now),
                'claim_receipt_canonical' => $receipt,
                'claim_receipt_sha256' => Task51CanonicalArtifact::sha256($receipt),
                'updated_at' => $this->dbTimestamp($now),
            ])) {
                throw $this->conflict('Stage B claim lost the global compare-and-swap.');
            }
            $this->repository->appendTransition([
                'execution_id' => $stageB['executionId'],
                'ordinal' => 1,
                'from_state' => self::ISSUED,
                'to_state' => self::CLAIMED,
                'state_version' => 1,
                'evidence_sha256' => $stageBHash,
                'occurred_at' => $this->dbTimestamp($now),
            ]);

            return $receipt;
        });
    }

    /** Same E is idempotent; a different E can never replace canonical C. */
    public function consume(
        #[\SensitiveParameter] string $rawRunnerExport,
        string $runnerExportEvidenceRef
    ): string
    {
        $export = Task51CanonicalArtifact::parseRunnerExport($rawRunnerExport);
        if (!Task51CanonicalArtifact::isEvidenceRef($runnerExportEvidenceRef)) {
            throw $this->invalid('Runner export evidence ref is invalid.');
        }
        $exportHash = Task51CanonicalArtifact::sha256($rawRunnerExport);

        return $this->repository->transaction(function () use ($export, $exportHash, $runnerExportEvidenceRef): string {
            $row = $this->repository->findExecution($export['executionId'], true);
            if ($row === null) {
                throw $this->invalid('Runner export execution was not issued by this coordinator.');
            }
            $this->assertStoredDeploymentBinding($row);
            $this->assertStoredProductionDirectMatrixBinding($row, $export);
            if (($row['state'] ?? null) === self::CONSUMED) {
                if (hash_equals((string)($row['runner_export_receipt_sha256'] ?? ''), $exportHash)
                    && hash_equals((string)($row['runner_export_receipt_ref'] ?? ''), $runnerExportEvidenceRef)) {
                    $storedReceipt = $row['consumption_receipt_canonical'] ?? null;
                    $storedReceiptHash = $row['consumption_receipt_sha256'] ?? null;
                    if (!is_string($storedReceipt) || $storedReceipt === ''
                        || !is_string($storedReceiptHash)
                        || !hash_equals($storedReceiptHash, Task51CanonicalArtifact::sha256($storedReceipt))) {
                        throw new Task51CoordinatorException(
                            Task51CoordinatorException::UNAVAILABLE,
                            'Stored consumption receipt is unavailable or corrupt.'
                        );
                    }
                    return $storedReceipt;
                }
                throw $this->conflict('A different runner export already consumed Stage B.');
            }
            if (!hash_equals((string)$row['approval_ref'], $export['approvalRef'])
                || !hash_equals((string)$row['stage_b_sha256'], $export['stageBExecutionEvidenceSha256'])) {
                throw $this->invalid('Runner export is not bound to the issued Stage B.');
            }
            if (($row['state'] ?? null) !== self::CLAIMED || (int)($row['state_version'] ?? -1) !== 1) {
                throw $this->conflict('Stage B must be claimed exactly once before consumption.');
            }
            // Take the DB clock sample after the lock for the same reason as
            // claim(): waiting for a row must never extend the valid window.
            $now = $this->repository->now();
            $expiresAt = $this->parseDbTimestamp((string)$row['expires_at']);
            $exportedAt = Task51CanonicalArtifact::parseTimestamp($export['exportedAt']);
            if ($now >= $expiresAt || $exportedAt >= $now) {
                throw $this->expired('Stage B or runner export timestamp is outside the authoritative window.');
            }
            $claimedAt = $this->parseDbTimestamp((string)$row['claimed_at']);
            if ($exportedAt < $claimedAt) {
                throw $this->invalid('Runner export predates the authoritative claim.');
            }
            $receipt = Task51CanonicalArtifact::encode([
                'approvalRef' => $export['approvalRef'],
                'consumedAt' => Task51CanonicalArtifact::formatTimestamp($now),
                'consumptionCount' => 1,
                'executionId' => $export['executionId'],
                'globalExactOneProved' => true,
                'runnerExportReceiptEvidenceRef' => $runnerExportEvidenceRef,
                'runnerExportReceiptEvidenceSha256' => $exportHash,
                'schema' => Task51CanonicalArtifact::CONSUMPTION_RECEIPT_SCHEMA,
                'stageBExecutionEvidenceSha256' => $export['stageBExecutionEvidenceSha256'],
                'startedAt' => Task51CanonicalArtifact::formatTimestamp($claimedAt),
                'status' => self::CONSUMED,
            ], Task51CanonicalArtifact::MAX_CONSUMPTION_RECEIPT_BYTES);
            if (!$this->repository->compareAndSwapState($export['executionId'], self::CLAIMED, 1, [
                'state' => self::CONSUMED,
                'state_version' => 2,
                'consumption_count' => 1,
                'consumed_at' => $this->dbTimestamp($now),
                'runner_export_receipt_ref' => $runnerExportEvidenceRef,
                'runner_export_receipt_sha256' => $exportHash,
                'consumption_receipt_canonical' => $receipt,
                'consumption_receipt_sha256' => Task51CanonicalArtifact::sha256($receipt),
                'updated_at' => $this->dbTimestamp($now),
            ])) {
                throw $this->conflict('Runner export lost the global compare-and-swap.');
            }
            $this->repository->appendTransition([
                'execution_id' => $export['executionId'],
                'ordinal' => 2,
                'from_state' => self::CLAIMED,
                'to_state' => self::CONSUMED,
                'state_version' => 2,
                'evidence_sha256' => $exportHash,
                'occurred_at' => $this->dbTimestamp($now),
            ]);

            return $receipt;
        });
    }

    /** @param array<string, mixed> $stageB */
    private function assertDeploymentBinding(#[\SensitiveParameter] array $stageB): void
    {
        if ($stageB['coordinatorOrigin'] !== Task51CanonicalArtifact::COORDINATOR_ORIGIN
            || !hash_equals($this->serverPublishSha, $stageB['coordinatorServerPublishSha'])) {
            throw $this->invalid('Stage B is bound to a different coordinator deployment.');
        }
    }

    /** @param array<string, mixed> $stageB @return array{0: DateTimeImmutable, 1: DateTimeImmutable} */
    private function assertIssueWindow(
        #[\SensitiveParameter] array $stageB,
        DateTimeImmutable $now
    ): array
    {
        $issuedAt = Task51CanonicalArtifact::parseTimestamp($stageB['issuedAt']);
        $expiresAt = Task51CanonicalArtifact::parseTimestamp($stageB['expiresAt']);
        if ($issuedAt > $now || $now >= $expiresAt
            || $expiresAt < $issuedAt->add(new DateInterval('PT' . self::MIN_WINDOW_SECONDS . 'S'))
            || $expiresAt > $issuedAt->add(new DateInterval('PT' . self::MAX_WINDOW_SECONDS . 'S'))) {
            throw $this->expired('Stage B issuance window is invalid or expired.');
        }
        return [$issuedAt, $expiresAt];
    }

    private function capabilityHash(#[\SensitiveParameter] string $capability): string
    {
        if (preg_match('/^[A-Za-z0-9_-]{43}$/D', $capability) !== 1) {
            throw $this->invalid('Claim capability must be a 256-bit base64url secret.');
        }
        $decoded = base64_decode(strtr($capability, '-_', '+/') . '=', true);
        if (!is_string($decoded) || strlen($decoded) !== 32) {
            throw $this->invalid('Claim capability must decode to exactly 256 bits.');
        }
        $canonical = rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=');
        if (!hash_equals($capability, $canonical)) {
            throw $this->invalid('Claim capability must use canonical unpadded base64url encoding.');
        }
        return hash('sha256', $capability);
    }

    /** @param array<string, mixed>|null $row @param array<string, mixed> $stageB */
    private function assertStoredBinding(
        ?array $row,
        #[\SensitiveParameter] array $stageB,
        string $stageBHash,
        string $capabilityHash
    ): void
    {
        if ($row === null
            || !hash_equals((string)$row['approval_ref'], $stageB['approvalRef'])
            || !hash_equals((string)$row['stage_b_sha256'], $stageBHash)
            || !hash_equals((string)$row['claim_capability_sha256'], $capabilityHash)
            || !hash_equals(
                (string)($row['production_direct_matrix_evidence_ref'] ?? ''),
                $stageB['productionDirectMatrixEvidenceRef']
            )
            || !hash_equals(
                (string)($row['production_direct_matrix_subject_digest'] ?? ''),
                $stageB['productionDirectMatrixSubjectDigest']
            )) {
            throw $this->invalid('Stage B is not issued by this coordinator.');
        }
    }

    /** @param array<string, mixed> $row @param array<string, mixed> $export */
    private function assertStoredProductionDirectMatrixBinding(
        array $row,
        #[\SensitiveParameter] array $export
    ): void {
        if (!hash_equals(
            (string)($row['production_direct_matrix_evidence_ref'] ?? ''),
            $export['productionDirectMatrixEvidenceRef']
        ) || !hash_equals(
            (string)($row['production_direct_matrix_subject_digest'] ?? ''),
            $export['productionDirectMatrixSubjectDigest']
        )) {
            throw $this->invalid(
                'Runner export Production direct matrix is not bound to the issued Stage B.'
            );
        }
    }

    /** @param array<string, mixed> $row */
    private function assertStoredDeploymentBinding(array $row): void
    {
        if (!hash_equals(
            Task51CanonicalArtifact::COORDINATOR_ORIGIN,
            (string)($row['coordinator_origin'] ?? '')
        ) || !hash_equals(
            $this->serverPublishSha,
            (string)($row['coordinator_server_publish_sha'] ?? '')
        )) {
            throw $this->invalid('Stored execution is bound to a different coordinator deployment.');
        }
    }

    /** @param array<string, mixed>|null $existing @param array<string, mixed> $expected */
    private function sameIssue(?array $existing, array $expected): bool
    {
        return $existing !== null
            && hash_equals((string)$existing['approval_ref'], (string)$expected['approval_ref'])
            && hash_equals((string)$existing['stage_b_sha256'], (string)$expected['stage_b_sha256'])
            && hash_equals(
                (string)$existing['claim_capability_sha256'],
                (string)$expected['claim_capability_sha256']
            )
            && hash_equals(
                (string)($existing['production_direct_matrix_evidence_ref'] ?? ''),
                (string)$expected['production_direct_matrix_evidence_ref']
            )
            && hash_equals(
                (string)($existing['production_direct_matrix_subject_digest'] ?? ''),
                (string)$expected['production_direct_matrix_subject_digest']
            );
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function safeIssueMetadata(array $row): array
    {
        return [
            'approvalRef' => $row['approval_ref'],
            'executionId' => $row['execution_id'],
            'expiresAt' => Task51CanonicalArtifact::formatTimestamp($this->parseDbTimestamp((string)$row['expires_at'])),
            'stageBExecutionEvidenceSha256' => $row['stage_b_sha256'],
            'state' => $row['state'],
        ];
    }

    private function dbTimestamp(DateTimeImmutable $value): string
    {
        return $value->format('Y-m-d H:i:s.v');
    }

    private function parseDbTimestamp(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value . ' UTC');
    }

    private function invalid(string $message): Task51CoordinatorException
    {
        return new Task51CoordinatorException(Task51CoordinatorException::INVALID, $message);
    }

    private function expired(string $message): Task51CoordinatorException
    {
        return new Task51CoordinatorException(Task51CoordinatorException::EXPIRED, $message);
    }

    private function conflict(string $message): Task51CoordinatorException
    {
        return new Task51CoordinatorException(Task51CoordinatorException::CONFLICT, $message);
    }
}
