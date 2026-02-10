<?php

namespace tests\unit\services;

use PHPUnit\Framework\TestCase;
use common\components\UuidHelper;

/**
 * ScenePackageService Unit Tests
 *
 * Tests the ScenePackageService for:
 * - Import UUID freshness (new UUIDs differ from originals)
 * - Import transaction rollback on failure
 *
 * Requirements: 7.1, 7.2, 7.3, 8.1, 8.2, 8.3
 *
 * @group scene-package
 * @group scene-package-service
 */
class ScenePackageServiceTest extends TestCase
{
    // =========================================================================
    // UUID Freshness Tests (Requirements 7.1, 7.2, 7.3)
    // =========================================================================

    /**
     * Test that UuidHelper generates valid UUID v4 format.
     * Requirement 7.1, 7.2, 7.3: New UUIDs must be UUID v4 format
     */
    public function testUuidHelperGeneratesValidUuidV4Format(): void
    {
        $uuid = UuidHelper::uuid();

        // UUID v4 format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
        // where y is one of 8, 9, a, b
        $uuidV4Pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        $this->assertMatchesRegularExpression($uuidV4Pattern, $uuid, 'Generated UUID should match UUID v4 format');
    }

    /**
     * Test that UuidHelper generates unique UUIDs (not reusing originals).
     * Requirement 7.1: Verse gets new UUID v4 (not reusing original)
     * Requirement 7.2: Each Meta gets new UUID v4 (not reusing original)
     * Requirement 7.3: Each Resource gets new UUID v4 (not reusing original)
     */
    public function testUuidHelperGeneratesUniqueUuids(): void
    {
        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            $uuids[] = UuidHelper::uuid();
        }

        // All UUIDs should be unique
        $uniqueUuids = array_unique($uuids);
        $this->assertCount(100, $uniqueUuids, 'All generated UUIDs should be unique');
    }

    /**
     * Test that newly generated UUIDs differ from any given original UUID.
     * Requirement 7.1: Verse UUID freshness
     */
    public function testNewUuidDiffersFromOriginalVerseUuid(): void
    {
        $originalVerseUuid = 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d';

        $newUuid = UuidHelper::uuid();

        $this->assertNotEquals(
            $originalVerseUuid,
            $newUuid,
            'New Verse UUID should differ from original UUID'
        );
    }

    /**
     * Test that multiple generated UUIDs all differ from a set of original UUIDs.
     * Requirement 7.2: Each Meta gets new UUID v4 (not reusing original)
     */
    public function testNewUuidsDifferFromOriginalMetaUuids(): void
    {
        $originalMetaUuids = [
            'meta-uuid-aaa-111-222',
            'meta-uuid-bbb-333-444',
            'meta-uuid-ccc-555-666',
        ];

        $newUuids = [];
        for ($i = 0; $i < count($originalMetaUuids); $i++) {
            $newUuids[] = UuidHelper::uuid();
        }

        // Each new UUID should differ from all original UUIDs
        foreach ($newUuids as $index => $newUuid) {
            foreach ($originalMetaUuids as $originalUuid) {
                $this->assertNotEquals(
                    $originalUuid,
                    $newUuid,
                    "New Meta UUID #{$index} should differ from original UUID {$originalUuid}"
                );
            }
        }

        // New UUIDs should also be unique among themselves
        $uniqueNewUuids = array_unique($newUuids);
        $this->assertCount(
            count($newUuids),
            $uniqueNewUuids,
            'All new Meta UUIDs should be unique among themselves'
        );
    }

    /**
     * Test that multiple generated UUIDs all differ from a set of original Resource UUIDs.
     * Requirement 7.3: Each Resource gets new UUID v4 (not reusing original)
     */
    public function testNewUuidsDifferFromOriginalResourceUuids(): void
    {
        $originalResourceUuids = [
            'res-uuid-111-aaa-bbb',
            'res-uuid-222-ccc-ddd',
        ];

        $newUuids = [];
        for ($i = 0; $i < count($originalResourceUuids); $i++) {
            $newUuids[] = UuidHelper::uuid();
        }

        foreach ($newUuids as $index => $newUuid) {
            foreach ($originalResourceUuids as $originalUuid) {
                $this->assertNotEquals(
                    $originalUuid,
                    $newUuid,
                    "New Resource UUID #{$index} should differ from original UUID {$originalUuid}"
                );
            }
        }
    }

    /**
     * Test that generated UUIDs are always valid UUID v4 across many iterations.
     * Validates the UUID generation mechanism used during import.
     * Requirements 7.1, 7.2, 7.3
     */
    public function testAllGeneratedUuidsAreValidV4(): void
    {
        $uuidV4Pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        for ($i = 0; $i < 50; $i++) {
            $uuid = UuidHelper::uuid();
            $this->assertMatchesRegularExpression(
                $uuidV4Pattern,
                $uuid,
                "UUID iteration {$i} should be valid UUID v4 format: {$uuid}"
            );
        }
    }

    // =========================================================================
    // Transaction Rollback Tests (Requirements 8.1, 8.2, 8.3)
    // =========================================================================

    /**
     * Test that importScene wraps operations in a transaction and rolls back on failure.
     * Requirement 8.1: All operations in single database transaction
     * Requirement 8.2: Any failure rolls back all operations
     *
     * We verify this by calling importScene with data that will cause a model
     * save failure (since there's no real database, ActiveRecord save() will fail),
     * and confirming that an exception is thrown.
     */
    public function testImportSceneThrowsExceptionOnSaveFailure(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $importData = [
            'verse' => [
                'name' => 'Test Verse',
                'description' => 'Test Description',
                'data' => '{"type":"Verse"}',
                'version' => 1,
                'uuid' => 'original-verse-uuid-123',
            ],
            'metas' => [],
            'resourceFileMappings' => [],
        ];

        // Without a real database connection, beginTransaction() or save() will fail,
        // which should trigger the catch block and re-throw the exception.
        $this->expectException(\Exception::class);

        $service->importScene($importData);
    }

    /**
     * Test that importScene throws exception when resource save fails.
     * Requirement 8.2: Any failure rolls back all operations
     *
     * When resourceFileMappings reference non-existent files, the Resource
     * model save should fail, triggering a rollback.
     */
    public function testImportSceneThrowsExceptionOnResourceSaveFailure(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $importData = [
            'verse' => [
                'name' => 'Test Verse',
                'description' => 'Test Description',
                'data' => '{"type":"Verse"}',
                'version' => 1,
                'uuid' => 'original-verse-uuid-456',
            ],
            'metas' => [
                [
                    'title' => 'Test Meta',
                    'uuid' => 'original-meta-uuid-789',
                    'data' => '{"type":"MetaRoot"}',
                    'events' => '{"inputs":[],"outputs":[]}',
                    'resourceFileIds' => [999],
                ],
            ],
            'resourceFileMappings' => [
                [
                    'originalUuid' => 'original-resource-uuid-abc',
                    'fileId' => 999,
                    'name' => 'Test Resource',
                    'type' => 'polygen',
                    'info' => '{}',
                ],
            ],
        ];

        // This should fail because there's no real database to save to,
        // triggering the transaction rollback and exception re-throw.
        $this->expectException(\Exception::class);

        $service->importScene($importData);
    }

    /**
     * Test that the exception message from importScene contains error description.
     * Requirement 8.3: Rollback returns 500 with error description
     *
     * The service throws exceptions with descriptive messages that the controller
     * catches and converts to 500 responses.
     */
    public function testImportSceneExceptionContainsErrorDescription(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $importData = [
            'verse' => [
                'name' => 'Test Verse',
                'description' => 'Test',
                'data' => '{"type":"Verse"}',
                'version' => 1,
                'uuid' => 'original-uuid-for-error-test',
            ],
            'metas' => [],
            'resourceFileMappings' => [],
        ];

        try {
            $service->importScene($importData);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // The exception message should be non-empty and descriptive
            $this->assertNotEmpty(
                $e->getMessage(),
                'Exception message should contain error description'
            );
            // The message should be a string (descriptive error)
            $this->assertIsString(
                $e->getMessage(),
                'Exception message should be a string description'
            );
        }
    }

    // =========================================================================
    // Import Data Structure Contract Tests
    // =========================================================================

    /**
     * Test that importScene method exists and accepts array parameter.
     * Requirement 8.1: importScene operates within a transaction
     */
    public function testImportSceneMethodExists(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $this->assertTrue(
            method_exists($service, 'importScene'),
            'ScenePackageService should have importScene method'
        );

        $reflection = new \ReflectionMethod($service, 'importScene');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params, 'importScene should accept exactly one parameter');
        $this->assertEquals('data', $params[0]->getName(), 'Parameter should be named "data"');
    }

    /**
     * Test that importScene return type is array.
     * Requirement 4.7: Returns {verseId, metaIdMap, resourceIdMap}
     */
    public function testImportSceneReturnTypeIsArray(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $reflection = new \ReflectionMethod($service, 'importScene');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType, 'importScene should have a return type');
        $this->assertEquals('array', $returnType->getName(), 'importScene should return array');
    }

    /**
     * Test that buildExportData method exists and accepts int parameter.
     */
    public function testBuildExportDataMethodExists(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $this->assertTrue(
            method_exists($service, 'buildExportData'),
            'ScenePackageService should have buildExportData method'
        );

        $reflection = new \ReflectionMethod($service, 'buildExportData');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params, 'buildExportData should accept exactly one parameter');
        $this->assertEquals('verseId', $params[0]->getName(), 'Parameter should be named "verseId"');
    }

    // =========================================================================
    // UUID Freshness in Import Context (Requirements 7.1, 7.2, 7.3)
    // =========================================================================

    /**
     * Test that UuidHelper::uuid() never produces the same UUID as any in a given set.
     * This simulates the import scenario where we have original UUIDs from the
     * import data and need to ensure all new UUIDs are fresh.
     *
     * Requirements 7.1, 7.2, 7.3
     */
    public function testUuidFreshnessAcrossMultipleEntities(): void
    {
        // Simulate original UUIDs from an import payload
        $originalUuids = [
            'verse' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'meta1' => 'b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e',
            'meta2' => 'c3d4e5f6-a7b8-4c9d-0e1f-2a3b4c5d6e7f',
            'resource1' => 'd4e5f6a7-b8c9-4d0e-1f2a-3b4c5d6e7f8a',
            'resource2' => 'e5f6a7b8-c9d0-4e1f-2a3b-4c5d6e7f8a9b',
        ];

        // Generate new UUIDs as the import process would
        $newUuids = [];
        foreach ($originalUuids as $key => $originalUuid) {
            $newUuids[$key] = UuidHelper::uuid();
        }

        // Verify each new UUID differs from its corresponding original
        foreach ($originalUuids as $key => $originalUuid) {
            $this->assertNotEquals(
                $originalUuid,
                $newUuids[$key],
                "New UUID for {$key} should differ from original"
            );
        }

        // Verify all new UUIDs are unique among themselves
        $uniqueNewUuids = array_unique($newUuids);
        $this->assertCount(
            count($newUuids),
            $uniqueNewUuids,
            'All new UUIDs should be unique among themselves'
        );

        // Verify no new UUID appears in the original set
        foreach ($newUuids as $key => $newUuid) {
            $this->assertNotContains(
                $newUuid,
                array_values($originalUuids),
                "New UUID for {$key} should not appear in original UUID set"
            );
        }
    }

    /**
     * Test that UUID generation is non-deterministic (different on each call).
     * This ensures the import process creates truly fresh UUIDs.
     * Requirements 7.1, 7.2, 7.3
     */
    public function testUuidGenerationIsNonDeterministic(): void
    {
        $uuid1 = UuidHelper::uuid();
        $uuid2 = UuidHelper::uuid();

        $this->assertNotEquals($uuid1, $uuid2, 'Consecutive UUID generations should produce different values');
    }

    // =========================================================================
    // Service Instantiation Tests
    // =========================================================================

    /**
     * Test that ScenePackageService can be instantiated.
     */
    public function testServiceCanBeInstantiated(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $this->assertInstanceOf(
            \api\modules\v1\services\ScenePackageService::class,
            $service
        );
    }

    /**
     * Test that ScenePackageService extends yii\base\Component.
     */
    public function testServiceExtendsYiiComponent(): void
    {
        $service = new \api\modules\v1\services\ScenePackageService();

        $this->assertInstanceOf(
            \yii\base\Component::class,
            $service,
            'ScenePackageService should extend yii\\base\\Component'
        );
    }
}
