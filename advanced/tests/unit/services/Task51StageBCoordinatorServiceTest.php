<?php

namespace tests\unit\services;

use api\modules\v1\services\Task51CanonicalArtifact;
use api\modules\v1\services\Task51CoordinatorException;
use api\modules\v1\services\Task51StageBCoordinatorService;
use api\modules\v1\services\Task51StageBRepositoryInterface;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use tests\support\Task51ArtifactFixture;
use Throwable;

require_once dirname(__DIR__, 2) . '/support/Task51ArtifactFixture.php';

final class Task51StageBCoordinatorServiceTest extends TestCase
{
    private const CAPABILITY = Task51ArtifactFixture::CAPABILITY;

    public function testIssueClaimConsumeAndSameExportReplayReturnOneCanonicalReceipt(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $issued = $service->issue($rawStageB, self::CAPABILITY);
        $this->assertSame('ISSUED', $issued['state']);

        $claimReceipt = $service->claim($rawStageB, self::CAPABILITY);
        $claim = json_decode($claimReceipt, true, 32, JSON_THROW_ON_ERROR);
        $this->assertSame('CLAIMED', $claim['state']);
        $this->assertTrue($claim['globalExactOneClaimed']);
        $this->assertSame(1, $claim['claimCount']);
        $this->assertArrayNotHasKey('claimCapabilitySha256', $claim);
        $this->assertStringNotContainsString(self::CAPABILITY, $claimReceipt);
        $this->assertSame("\n", substr($claimReceipt, -1));
        $this->assertFalse(str_ends_with($claimReceipt, "\n\n"));
        $this->assertSame(
            $claimReceipt,
            Task51CanonicalArtifact::encode($claim, Task51CanonicalArtifact::MAX_CLAIM_RECEIPT_BYTES)
        );

        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');
        $rawExport = $this->rawRunnerExport($rawStageB);
        $stageB = json_decode($rawStageB, true, 32, JSON_THROW_ON_ERROR);
        $export = json_decode($rawExport, true, 32, JSON_THROW_ON_ERROR);
        $this->assertSame(
            $stageB['productionDirectMatrixEvidenceRef'],
            $export['productionDirectMatrixEvidenceRef']
        );
        $this->assertSame(
            $stageB['productionDirectMatrixSubjectDigest'],
            $export['productionDirectMatrixSubjectDigest']
        );
        $this->assertSame(
            Task51ArtifactFixture::MATRIX_EVIDENCE_SHA256,
            $export['productionDirectMatrixEvidenceSha256']
        );
        $receipt = $service->consume($rawExport, 'reports/task51-export.json');
        $repository->clock = new DateTimeImmutable('2026-08-28T09:30:00.000Z');
        $replayed = $service->consume($rawExport, 'reports/task51-export.json');
        $this->assertSame($receipt, $replayed);
        $consumption = json_decode($receipt, true, 32, JSON_THROW_ON_ERROR);
        $this->assertSame('CONSUMED', $consumption['status']);
        $this->assertTrue($consumption['globalExactOneProved']);
        $this->assertSame(1, $consumption['consumptionCount']);
        $this->assertSame($claim['claimedAt'], $consumption['startedAt']);
        $this->assertSame(
            Task51CanonicalArtifact::sha256($rawStageB),
            $consumption['stageBExecutionEvidenceSha256']
        );
        $this->assertSame(
            Task51CanonicalArtifact::sha256($rawExport),
            $consumption['runnerExportReceiptEvidenceSha256']
        );
        $this->assertSame(
            'reports/task51-export.json',
            $consumption['runnerExportReceiptEvidenceRef']
        );
        $stored = $repository->rows['task51-stage-b-20260828-test'];
        $this->assertSame(
            $stageB['productionDirectMatrixEvidenceRef'],
            $stored['production_direct_matrix_evidence_ref']
        );
        $this->assertSame(
            $stageB['productionDirectMatrixSubjectDigest'],
            $stored['production_direct_matrix_subject_digest']
        );
        $this->assertSame(
            Task51CanonicalArtifact::sha256($rawExport),
            $stored['runner_export_receipt_sha256']
        );
        $this->assertSame(
            Task51CanonicalArtifact::sha256($receipt),
            $stored['consumption_receipt_sha256']
        );
        $this->assertCount(3, $repository->transitions);
    }

    public function testSecondClaimLosesGlobalCasEvenWithSameCapability(): void
    {
        [$service, , $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);

        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('already been used');
        $service->claim($rawStageB, self::CAPABILITY);
    }

    public function testIssueAtSameMillisecondDoesNotTreatMySqlNoOpAsLostCas(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();

        $issued = $service->issue($rawStageB, self::CAPABILITY);

        $this->assertSame('ISSUED', $issued['state']);
        $this->assertSame(0, $repository->compareAndSwapCalls);
        $this->assertCount(1, $repository->transitions);
        $this->assertSame(
            '2026-08-28 08:00:00.000',
            $repository->rows['task51-stage-b-20260828-test']['created_at']
        );
    }

    public function testRepositoryCasAbstractionPermitsOnlyOneConcurrentWinner(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);

        $first = $repository->compareAndSwapState(
            'task51-stage-b-20260828-test',
            'ISSUED',
            0,
            ['state' => 'CLAIMED', 'state_version' => 1]
        );
        $second = $repository->compareAndSwapState(
            'task51-stage-b-20260828-test',
            'ISSUED',
            0,
            ['state' => 'CLAIMED', 'state_version' => 1]
        );

        $this->assertTrue($first);
        $this->assertFalse($second);
    }

    public function testProductionAdapterUsesOneSharedDatabaseTransactionAndVersionedCas(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3)
            . '/api/modules/v1/services/DbTask51StageBRepository.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('beginTransaction()', $source);
        $this->assertStringContainsString("'state' => \$expectedState", $source);
        $this->assertStringContainsString("'state_version' => \$expectedVersion", $source);
        $this->assertStringContainsString("{{%task51_stage_b_execution}}", $source);
        $this->assertStringContainsString("{{%task51_stage_b_transition}}", $source);
        $this->assertStringNotContainsString('redis', strtolower($source));
        $this->assertStringNotContainsString('cache', strtolower($source));
    }

    public function testWrongCapabilityNeverClaims(): void
    {
        [$service, , $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);

        $this->expectException(Task51CoordinatorException::class);
        $service->claim($rawStageB, 'ZGRkZGRkZGRkZGRkZGRkZGRkZGRkZGRkZGRkZGRkZGQ');
    }

    public function testNonCanonicalBase64UrlPadBitsNeverIssue(): void
    {
        [$service, , $rawStageB] = $this->fixture();
        $nonCanonical = substr(self::CAPABILITY, 0, -1) . 'N';
        $this->assertSame(
            base64_decode(strtr(self::CAPABILITY, '-_', '+/') . '=', true),
            base64_decode(strtr($nonCanonical, '-_', '+/') . '=', true)
        );

        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('canonical unpadded base64url');
        $service->issue($rawStageB, $nonCanonical);
    }

    public function testExpiredClaimNeverChangesIssuedState(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:40:00.000Z');
        try {
            $service->claim($rawStageB, self::CAPABILITY);
            $this->fail('Expected insufficient remaining time to reject claim.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::EXPIRED, $exception->reason());
        }
        $this->assertSame('ISSUED', $repository->rows['task51-stage-b-20260828-test']['state']);
    }

    public function testClaimBoundaryPreservesMilliseconds(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);

        $repository->clock = new DateTimeImmutable('2026-08-28T08:28:45.001Z');
        try {
            $service->claim($rawStageB, self::CAPABILITY);
            $this->fail('Expected the sub-millisecond-short claim window to be rejected.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::EXPIRED, $exception->reason());
        }
        $this->assertSame('ISSUED', $repository->rows['task51-stage-b-20260828-test']['state']);

        $repository->clock = new DateTimeImmutable('2026-08-28T08:28:45.000Z');
        $this->assertNotSame('', $service->claim($rawStageB, self::CAPABILITY));
    }

    public function testIssueWindowBoundaryPreservesMilliseconds(): void
    {
        $stageB = Task51ArtifactFixture::stageB();
        $stageB['issuedAt'] = '2026-08-28T08:00:00.001Z';
        $stageB['expiresAt'] = '2026-08-28T08:35:00.000Z';
        $rawStageB = Task51CanonicalArtifact::encode($stageB, Task51CanonicalArtifact::MAX_STAGE_B_BYTES);
        $repository = new InMemoryTask51Repository(new DateTimeImmutable('2026-08-28T08:00:00.001Z'));
        $service = new Task51StageBCoordinatorService($repository, str_repeat('a', 40));

        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('issuance window');
        $service->issue($rawStageB, self::CAPABILITY);
    }

    public function testTransitionFailureRollsBackStateAndReceiptAtomically(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $repository->failNextTransition = true;
        try {
            $service->claim($rawStageB, self::CAPABILITY);
            $this->fail('Expected transition append failure.');
        } catch (\RuntimeException) {
            $this->assertSame('ISSUED', $repository->rows['task51-stage-b-20260828-test']['state']);
            $this->assertArrayNotHasKey('claim_receipt_canonical', $repository->rows['task51-stage-b-20260828-test']);
        }
        $this->assertNotSame('', $service->claim($rawStageB, self::CAPABILITY));
    }

    public function testDifferentExportCannotReplaceConsumedReceipt(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');
        $rawExport = $this->rawRunnerExport($rawStageB);
        $service->consume($rawExport, 'reports/task51-export.json');
        $changed = json_decode($rawExport, true, 32, JSON_THROW_ON_ERROR);
        $changed['runnerResultEvidenceSha256'] = str_repeat('1', 64);

        $this->expectException(Task51CoordinatorException::class);
        $service->consume(
            Task51CanonicalArtifact::encode($changed, Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES),
            'reports/task51-export.json'
        );
    }

    #[DataProvider('runnerExportMatrixBindingDrift')]
    public function testConsumeRejectsRunnerExportMatrixBindingDriftBeforeBurningStageB(
        string $key,
        string $drift
    ): void {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');
        $export = json_decode(
            $this->rawRunnerExport($rawStageB),
            true,
            32,
            JSON_THROW_ON_ERROR
        );
        $export[$key] = $drift;

        try {
            $service->consume(
                Task51CanonicalArtifact::encode(
                    $export,
                    Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
                ),
                'reports/task51-export.json'
            );
            $this->fail('A runner export with a drifted matrix binding must be rejected.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::INVALID, $exception->reason());
            $this->assertStringContainsString(
                'matrix is not bound to the issued Stage B',
                $exception->getMessage()
            );
        }

        $row = $repository->rows['task51-stage-b-20260828-test'];
        $this->assertSame('CLAIMED', $row['state']);
        $this->assertSame(0, $row['consumption_count']);
        $this->assertArrayNotHasKey('runner_export_receipt_sha256', $row);
        $this->assertCount(2, $repository->transitions);
    }

    /** @return iterable<string, array{0: string, 1: string}> */
    public static function runnerExportMatrixBindingDrift(): iterable
    {
        yield 'matrix evidence ref' => [
            'productionDirectMatrixEvidenceRef',
            'reports/task51-other-production-direct-matrix.json',
        ];
        yield 'matrix subject digest' => [
            'productionDirectMatrixSubjectDigest',
            str_repeat('7', 64),
        ];
    }

    public function testConsumeRequiresExportTimestampStrictlyBeforeDatabaseClock(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:20:00.000Z');

        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('authoritative window');
        $service->consume(
            $this->rawRunnerExport($rawStageB),
            'reports/task51-export.json'
        );
    }

    public function testIdempotentConsumeFailsClosedOnStoredReceiptCorruption(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');
        $rawExport = $this->rawRunnerExport($rawStageB);
        $service->consume($rawExport, 'reports/task51-export.json');
        $repository->rows['task51-stage-b-20260828-test']['consumption_receipt_canonical'] = "{}\n";

        try {
            $service->consume($rawExport, 'reports/task51-export.json');
            $this->fail('Expected corrupt stored receipt to fail closed.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::UNAVAILABLE, $exception->reason());
        }
    }

    public function testOldDeploymentCannotConsumeOrReplayWithNewServerSha(): void
    {
        [$currentService, $repository, $rawStageB] = $this->fixture();
        $currentService->issue($rawStageB, self::CAPABILITY);
        $currentService->claim($rawStageB, self::CAPABILITY);
        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');
        $rawExport = $this->rawRunnerExport($rawStageB);
        $newReleaseService = new Task51StageBCoordinatorService($repository, str_repeat('b', 40));

        try {
            $newReleaseService->consume($rawExport, 'reports/task51-export.json');
            $this->fail('A new release must not consume an old release row.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::INVALID, $exception->reason());
            $this->assertStringContainsString('different coordinator deployment', $exception->getMessage());
        }
        $this->assertSame('CLAIMED', $repository->rows['task51-stage-b-20260828-test']['state']);

        $receipt = $currentService->consume($rawExport, 'reports/task51-export.json');
        $this->assertNotSame('', $receipt);
        try {
            $newReleaseService->consume($rawExport, 'reports/task51-export.json');
            $this->fail('A new release must not replay an old release consumption receipt.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::INVALID, $exception->reason());
        }
    }

    public function testStoredCoordinatorOriginMismatchNeverConsumes(): void
    {
        [$service, $repository, $rawStageB] = $this->fixture();
        $service->issue($rawStageB, self::CAPABILITY);
        $service->claim($rawStageB, self::CAPABILITY);
        $repository->rows['task51-stage-b-20260828-test']['coordinator_origin'] = 'https://wrong.example';
        $repository->clock = new DateTimeImmutable('2026-08-28T08:21:00.000Z');

        $this->expectException(Task51CoordinatorException::class);
        $this->expectExceptionMessage('different coordinator deployment');
        $service->consume($this->rawRunnerExport($rawStageB), 'reports/task51-export.json');
    }

    /** @return array{0: Task51StageBCoordinatorService, 1: InMemoryTask51Repository, 2: string} */
    private function fixture(): array
    {
        $repository = new InMemoryTask51Repository(new DateTimeImmutable('2026-08-28T08:00:00.000Z'));
        $service = new Task51StageBCoordinatorService($repository, str_repeat('a', 40));
        $rawStageB = Task51CanonicalArtifact::encode(
            Task51ArtifactFixture::stageB(),
            Task51CanonicalArtifact::MAX_STAGE_B_BYTES
        );
        return [$service, $repository, $rawStageB];
    }

    private function rawRunnerExport(string $rawStageB): string
    {
        $value = Task51ArtifactFixture::runnerExport($rawStageB);
        return Task51CanonicalArtifact::encode($value, Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES);
    }
}

final class InMemoryTask51Repository implements Task51StageBRepositoryInterface
{
    /** @var array<string, array<string, mixed>> */
    public array $rows = [];
    /** @var list<array<string, mixed>> */
    public array $transitions = [];
    public bool $failNextTransition = false;
    public int $compareAndSwapCalls = 0;

    public function __construct(public DateTimeImmutable $clock)
    {
    }

    public function transaction(Closure $operation): mixed
    {
        $rows = $this->rows;
        $transitions = $this->transitions;
        try {
            return $operation();
        } catch (Throwable $exception) {
            $this->rows = $rows;
            $this->transitions = $transitions;
            throw $exception;
        }
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock;
    }

    public function insertExecution(array $row): bool
    {
        foreach ($this->rows as $existing) {
            if ($existing['execution_id'] === $row['execution_id']
                || $existing['approval_ref'] === $row['approval_ref']
                || $existing['stage_b_sha256'] === $row['stage_b_sha256']) {
                return false;
            }
        }
        $this->rows[$row['execution_id']] = $row;
        return true;
    }

    public function findExecution(string $executionId, bool $forUpdate = false): ?array
    {
        return $this->rows[$executionId] ?? null;
    }

    public function compareAndSwapState(
        string $executionId,
        string $expectedState,
        int $expectedVersion,
        array $changes
    ): bool {
        $this->compareAndSwapCalls++;
        $row = $this->rows[$executionId] ?? null;
        if ($row === null || $row['state'] !== $expectedState || (int)$row['state_version'] !== $expectedVersion) {
            return false;
        }
        $updated = array_merge($row, $changes);
        if ($updated === $row) {
            return false;
        }
        $this->rows[$executionId] = $updated;
        return true;
    }

    public function appendTransition(array $row): void
    {
        if ($this->failNextTransition) {
            $this->failNextTransition = false;
            throw new \RuntimeException('simulated append failure');
        }
        $this->transitions[] = $row;
    }
}
