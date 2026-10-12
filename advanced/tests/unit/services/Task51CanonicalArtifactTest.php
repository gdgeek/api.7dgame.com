<?php

namespace tests\unit\services;

use api\modules\v1\services\Task51ArtifactException;
use api\modules\v1\services\Task51CanonicalArtifact;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use tests\support\Task51ArtifactFixture;

require_once dirname(__DIR__, 2) . '/support/Task51ArtifactFixture.php';

final class Task51CanonicalArtifactTest extends TestCase
{
    public function testStageBRequiresExactAsciiSortedCanonicalBytesAndV3Bindings(): void
    {
        $stageB = self::stageB();
        $raw = Task51CanonicalArtifact::encode($stageB, Task51CanonicalArtifact::MAX_STAGE_B_BYTES);

        $this->assertSame($stageB, Task51CanonicalArtifact::parseStageB($raw));
        $this->assertStringStartsWith('{"approvalRef":', $raw);
        $this->assertSame("\n", substr($raw, -1));
        $this->assertDoesNotMatchRegularExpression('/[^\x00-\x7f]/', $raw);
    }

    #[DataProvider('invalidStageBBytes')]
    public function testStageBRejectsNonCanonicalOrMalformedBytes(string $raw): void
    {
        $this->expectException(Task51ArtifactException::class);
        Task51CanonicalArtifact::parseStageB($raw);
    }

    /** @return iterable<string, array{0: string}> */
    public static function invalidStageBBytes(): iterable
    {
        $valid = Task51CanonicalArtifact::encode(self::stageB(), Task51CanonicalArtifact::MAX_STAGE_B_BYTES);
        yield 'missing LF' => [rtrim($valid, "\n")];
        yield 'extra LF' => [$valid . "\n"];
        yield 'leading whitespace' => [' ' . $valid];
        yield 'key order changed' => [str_replace(
            '{"approvalRef":"WP3-TASK51-MEMORY-RUNNER-STAGE-B-20260828",',
            '{"schema":"wp3-task51-stage-b-execution-approval-v3","approvalRef":"WP3-TASK51-MEMORY-RUNNER-STAGE-B-20260828",',
            str_replace('"schema":"wp3-task51-stage-b-execution-approval-v3",', '', $valid)
        )];
        yield 'unknown key' => [str_replace('{', '{"extra":true,', $valid)];
        yield 'non ASCII raw' => [str_replace('APPROVED', '批准', $valid)];
        yield 'invalid timestamp' => [str_replace('2026-08-28T07:59:00.000Z', 'not-a-time', $valid)];
        yield 'non-increasing approval window' => [str_replace(
            '2026-08-28T08:59:00.000Z',
            '2026-08-28T07:59:00.000Z',
            $valid
        )];
        yield 'oversize' => [str_repeat(' ', Task51CanonicalArtifact::MAX_STAGE_B_BYTES + 1)];
    }

    #[DataProvider('invalidStageBMatrixBindings')]
    public function testStageBRejectsInvalidProductionDirectMatrixBindings(
        string $key,
        mixed $invalidValue
    ): void {
        $stageB = self::stageB();
        $stageB[$key] = $invalidValue;
        $raw = Task51CanonicalArtifact::encode(
            $stageB,
            Task51CanonicalArtifact::MAX_STAGE_B_BYTES
        );

        $this->expectException(Task51ArtifactException::class);
        Task51CanonicalArtifact::parseStageB($raw);
    }

    /** @return iterable<string, array{0: string, 1: mixed}> */
    public static function invalidStageBMatrixBindings(): iterable
    {
        yield 'matrix ref traversal' => [
            'productionDirectMatrixEvidenceRef',
            'reports/../x.json',
        ];
        yield 'matrix ref dot segment' => [
            'productionDirectMatrixEvidenceRef',
            'reports/./x.json',
        ];
        yield 'matrix ref empty segment' => [
            'productionDirectMatrixEvidenceRef',
            'reports//x.json',
        ];
        yield 'matrix ref must be json' => [
            'productionDirectMatrixEvidenceRef',
            'reports/x.txt',
        ];
        yield 'matrix cell count drift' => [
            'productionDirectMatrixAuthorizedCellCount',
            255,
        ];
        yield 'zero matrix subject digest' => [
            'productionDirectMatrixSubjectDigest',
            str_repeat('0', 64),
        ];
        yield 'zero Stage A evidence sha' => [
            'stageAReleaseEvidenceSha256',
            str_repeat('0', 64),
        ];
    }

    public function testRunnerExportRejectsGlobalProofPrepopulationAndUnsafeEvidenceRefs(): void
    {
        $export = self::runnerExport();
        $export['globalExactOneProved'] = true;
        $raw = Task51CanonicalArtifact::encode($export, Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES);

        $this->expectException(Task51ArtifactException::class);
        Task51CanonicalArtifact::parseRunnerExport($raw);
    }

    #[DataProvider('invalidRunnerExportMatrixBindings')]
    public function testRunnerExportRejectsInvalidProductionDirectMatrixBindings(
        string $key,
        mixed $invalidValue
    ): void {
        $export = self::runnerExport();
        $export[$key] = $invalidValue;
        $raw = Task51CanonicalArtifact::encode(
            $export,
            Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
        );

        $this->expectException(Task51ArtifactException::class);
        Task51CanonicalArtifact::parseRunnerExport($raw);
    }

    /** @return iterable<string, array{0: string, 1: mixed}> */
    public static function invalidRunnerExportMatrixBindings(): iterable
    {
        yield 'matrix ref traversal' => [
            'productionDirectMatrixEvidenceRef',
            'reports/../x.json',
        ];
        yield 'matrix ref must be json' => [
            'productionDirectMatrixEvidenceRef',
            'reports/x.txt',
        ];
        yield 'matrix sha malformed' => [
            'productionDirectMatrixEvidenceSha256',
            str_repeat('9', 63),
        ];
        yield 'matrix subject digest malformed' => [
            'productionDirectMatrixSubjectDigest',
            str_repeat('z', 64),
        ];
        yield 'zero matrix evidence sha' => [
            'productionDirectMatrixEvidenceSha256',
            str_repeat('0', 64),
        ];
        yield 'zero matrix subject digest' => [
            'productionDirectMatrixSubjectDigest',
            str_repeat('0', 64),
        ];
    }

    public function testRunnerExportRejectsUnknownKeys(): void
    {
        $export = self::runnerExport();
        $export['unexpectedMatrixBinding'] = true;
        $raw = Task51CanonicalArtifact::encode(
            $export,
            Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
        );

        $this->expectException(Task51ArtifactException::class);
        Task51CanonicalArtifact::parseRunnerExport($raw);
    }

    public function testSharedFixturesTrackTheExactStageBAndRunnerExportContracts(): void
    {
        $stageB = Task51ArtifactFixture::stageB();
        $rawStageB = Task51CanonicalArtifact::encode($stageB, Task51CanonicalArtifact::MAX_STAGE_B_BYTES);
        $this->assertSame($stageB, Task51CanonicalArtifact::parseStageB($rawStageB));
        $this->assertArrayHasKey('stageACoordinatorServerReleaseEvidenceSha256', $stageB);
        $this->assertArrayHasKey('stageANetworkAttestorReleaseEvidenceSha256', $stageB);
        $this->assertArrayHasKey('productionDirectMatrixSubjectDigest', $stageB);
        $this->assertSame(
            'wp3-task51-runner-fragment-v3',
            Task51CanonicalArtifact::RUNNER_FRAGMENT_SCHEMA
        );
        $this->assertSame(
            'wp3-task51-stage-b-runner-result-v3',
            Task51CanonicalArtifact::RUNNER_RESULT_SCHEMA
        );
        $this->assertSame(
            'wp3-task51-stage-b-runner-export-receipt-v3',
            Task51CanonicalArtifact::RUNNER_EXPORT_SCHEMA
        );
        $this->assertSame(
            'wp3-task51-production-direct-matrix-v1',
            Task51CanonicalArtifact::PRODUCTION_DIRECT_MATRIX_SCHEMA
        );

        $runnerExport = Task51ArtifactFixture::runnerExport($rawStageB);
        $rawRunnerExport = Task51CanonicalArtifact::encode(
            $runnerExport,
            Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
        );
        $this->assertSame($runnerExport, Task51CanonicalArtifact::parseRunnerExport($rawRunnerExport));
        $this->assertArrayHasKey('stageBNetworkReceiptEvidenceRef', $runnerExport);
        $this->assertArrayHasKey('stageBNetworkReceiptEvidenceSha256', $runnerExport);
        $this->assertSame(
            $stageB['productionDirectMatrixEvidenceRef'],
            $runnerExport['productionDirectMatrixEvidenceRef']
        );
        $this->assertSame(
            Task51ArtifactFixture::MATRIX_EVIDENCE_SHA256,
            $runnerExport['productionDirectMatrixEvidenceSha256']
        );
        $this->assertSame(
            $stageB['productionDirectMatrixSubjectDigest'],
            $runnerExport['productionDirectMatrixSubjectDigest']
        );
        $this->assertNotSame(
            $runnerExport['productionDirectMatrixEvidenceSha256'],
            $runnerExport['productionDirectMatrixSubjectDigest']
        );
    }

    /** @return array<string, mixed> */
    public static function stageB(): array
    {
        return Task51ArtifactFixture::stageB();
    }

    /** @return array<string, mixed> */
    public static function runnerExport(): array
    {
        $rawStageB = Task51CanonicalArtifact::encode(
            Task51ArtifactFixture::stageB(),
            Task51CanonicalArtifact::MAX_STAGE_B_BYTES
        );
        return Task51ArtifactFixture::runnerExport($rawStageB);
    }
}
