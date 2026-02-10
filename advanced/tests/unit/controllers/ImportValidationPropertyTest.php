<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\ScenePackageController;
use Eris;
use Eris\Generators;
use PHPUnit\Framework\TestCase;
use yii\web\BadRequestHttpException;

/**
 * Import Validation Property-Based Tests
 *
 * Uses Eris (PHP QuickCheck) to verify that the import data validation logic
 * correctly rejects incomplete data by throwing BadRequestHttpException (400).
 *
 * Tests the private validateImportData() method via PHP Reflection.
 *
 * @group scene-package
 * @group import-validation
 * @group property-based-test
 */
class ImportValidationPropertyTest extends TestCase
{
    use Eris\TestTrait;

    /**
     * @var \ReflectionMethod
     */
    private \ReflectionMethod $validateMethod;

    /**
     * @var ScenePackageController
     */
    private ScenePackageController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ScenePackageController('scene-package', null);

        $reflection = new \ReflectionClass(ScenePackageController::class);
        $this->validateMethod = $reflection->getMethod('validateImportData');
        $this->validateMethod->setAccessible(true);
    }

    // =========================================================================
    // Helper: Build a complete valid import data structure
    // =========================================================================

    /**
     * Build a valid import data structure with all required fields.
     *
     * @param string $verseName
     * @param string $verseData
     * @param int    $verseVersion
     * @param string $verseUuid
     * @param string $metaTitle
     * @param string $metaUuid
     * @param string $mappingOriginalUuid
     * @param int    $mappingFileId
     * @param string $mappingName
     * @param string $mappingType
     * @param string $mappingInfo
     * @return array
     */
    private static function buildValidImportData(
        string $verseName = 'Test Scene',
        string $verseData = '{"type":"Verse"}',
        int    $verseVersion = 1,
        string $verseUuid = 'verse-uuid-001',
        string $metaTitle = 'Meta A',
        string $metaUuid = 'meta-uuid-001',
        string $mappingOriginalUuid = 'res-uuid-001',
        int    $mappingFileId = 100,
        string $mappingName = 'Model A',
        string $mappingType = 'polygen',
        string $mappingInfo = '{"size":1024}'
    ): array {
        return [
            'verse' => [
                'name' => $verseName,
                'data' => $verseData,
                'version' => $verseVersion,
                'uuid' => $verseUuid,
            ],
            'metas' => [
                [
                    'title' => $metaTitle,
                    'uuid' => $metaUuid,
                ],
            ],
            'resourceFileMappings' => [
                [
                    'originalUuid' => $mappingOriginalUuid,
                    'fileId' => $mappingFileId,
                    'name' => $mappingName,
                    'type' => $mappingType,
                    'info' => $mappingInfo,
                ],
            ],
        ];
    }

    /**
     * Invoke the private validateImportData method.
     *
     * @param mixed $data
     * @throws BadRequestHttpException
     */
    private function callValidateImportData($data): void
    {
        $this->validateMethod->invoke($this->controller, $data);
    }

    // =========================================================================
    // Property 7: 导入数据验证拒绝不完整数据 — verse 必填字段
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.2, 9.3, 9.4
     *
     * For any import data where the verse object is missing any one of the
     * required fields (name, data, version, uuid), the validation logic
     * should reject the data with a BadRequestHttpException (400).
     */
    public function testRejectsVerseWithMissingRequiredField(): void
    {
        $verseRequiredFields = ['name', 'data', 'version', 'uuid'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($verseRequiredFields),
                Generators::string(),
                Generators::string(),
                Generators::choose(1, 100),
                Generators::string()
            )
            ->when(function (string $_, string $name, string $data, int $version, string $uuid): bool {
                // Ensure generated values are non-empty so only the removed field causes failure
                return strlen($name) > 0 && strlen($data) > 0 && strlen($uuid) > 0;
            })
            ->then(function (
                string $fieldToRemove,
                string $name,
                string $data,
                int    $version,
                string $uuid
            ): void {
                $importData = self::buildValidImportData(
                    verseName: $name,
                    verseData: $data,
                    verseVersion: $version,
                    verseUuid: $uuid
                );

                // Remove the selected required field from verse
                unset($importData['verse'][$fieldToRemove]);

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.2
     *
     * For any import data where a verse required field is set to empty string,
     * the validation logic should reject the data with BadRequestHttpException.
     */
    public function testRejectsVerseWithEmptyRequiredField(): void
    {
        $verseRequiredFields = ['name', 'data', 'version', 'uuid'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($verseRequiredFields)
            )
            ->then(function (string $fieldToEmpty): void {
                $importData = self::buildValidImportData();

                // Set the selected required field to empty string
                $importData['verse'][$fieldToEmpty] = '';

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    // =========================================================================
    // Property 7: 导入数据验证拒绝不完整数据 — metas 必填字段
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.3
     *
     * For any import data where a metas element is missing any one of the
     * required fields (title, uuid), the validation logic should reject
     * the data with a BadRequestHttpException (400).
     */
    public function testRejectsMetaWithMissingRequiredField(): void
    {
        $metaRequiredFields = ['title', 'uuid'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($metaRequiredFields),
                Generators::choose(1, 5),
                Generators::string(),
                Generators::string()
            )
            ->when(function (string $_, int $__, string $title, string $uuid): bool {
                return strlen($title) > 0 && strlen($uuid) > 0;
            })
            ->then(function (
                string $fieldToRemove,
                int    $metaCount,
                string $title,
                string $uuid
            ): void {
                $importData = self::buildValidImportData(metaTitle: $title, metaUuid: $uuid);

                // Build multiple metas, all valid
                $metas = [];
                for ($i = 0; $i < $metaCount; $i++) {
                    $metas[] = [
                        'title' => $title . '_' . $i,
                        'uuid' => $uuid . '_' . $i,
                    ];
                }

                // Remove the required field from the last meta element
                $targetIndex = $metaCount - 1;
                unset($metas[$targetIndex][$fieldToRemove]);

                $importData['metas'] = $metas;

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.3
     *
     * For any import data where a metas element has an empty string for a
     * required field, the validation logic should reject the data.
     */
    public function testRejectsMetaWithEmptyRequiredField(): void
    {
        $metaRequiredFields = ['title', 'uuid'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($metaRequiredFields),
                Generators::choose(1, 3)
            )
            ->then(function (string $fieldToEmpty, int $metaCount): void {
                $importData = self::buildValidImportData();

                $metas = [];
                for ($i = 0; $i < $metaCount; $i++) {
                    $metas[] = [
                        'title' => 'Meta_' . $i,
                        'uuid' => 'meta-uuid-' . $i,
                    ];
                }

                // Set the required field to empty string on the last meta
                $targetIndex = $metaCount - 1;
                $metas[$targetIndex][$fieldToEmpty] = '';

                $importData['metas'] = $metas;

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    // =========================================================================
    // Property 7: 导入数据验证拒绝不完整数据 — resourceFileMappings 必填字段
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.4
     *
     * For any import data where a resourceFileMappings element is missing any
     * one of the required fields (originalUuid, fileId, name, type, info),
     * the validation logic should reject the data with BadRequestHttpException.
     */
    public function testRejectsResourceFileMappingWithMissingRequiredField(): void
    {
        $mappingRequiredFields = ['originalUuid', 'fileId', 'name', 'type', 'info'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($mappingRequiredFields),
                Generators::choose(1, 5),
                Generators::string(),
                Generators::choose(1, 9999),
                Generators::string(),
                Generators::string(),
                Generators::string()
            )
            ->when(function (
                string $_field,
                int    $_count,
                string $origUuid,
                int    $_fileId,
                string $name,
                string $type,
                string $info
            ): bool {
                return strlen($origUuid) > 0 && strlen($name) > 0
                    && strlen($type) > 0 && strlen($info) > 0;
            })
            ->then(function (
                string $fieldToRemove,
                int    $mappingCount,
                string $origUuid,
                int    $fileId,
                string $name,
                string $type,
                string $info
            ): void {
                $importData = self::buildValidImportData();

                // Build multiple mappings, all valid
                $mappings = [];
                for ($i = 0; $i < $mappingCount; $i++) {
                    $mappings[] = [
                        'originalUuid' => $origUuid . '_' . $i,
                        'fileId' => $fileId + $i,
                        'name' => $name . '_' . $i,
                        'type' => $type,
                        'info' => $info,
                    ];
                }

                // Remove the required field from the last mapping element
                $targetIndex = $mappingCount - 1;
                unset($mappings[$targetIndex][$fieldToRemove]);

                $importData['resourceFileMappings'] = $mappings;

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.4
     *
     * For any import data where a resourceFileMappings element has an empty
     * string for a required field, the validation logic should reject the data.
     */
    public function testRejectsResourceFileMappingWithEmptyRequiredField(): void
    {
        $mappingRequiredFields = ['originalUuid', 'fileId', 'name', 'type', 'info'];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($mappingRequiredFields),
                Generators::choose(1, 3)
            )
            ->then(function (string $fieldToEmpty, int $mappingCount): void {
                $importData = self::buildValidImportData();

                $mappings = [];
                for ($i = 0; $i < $mappingCount; $i++) {
                    $mappings[] = [
                        'originalUuid' => 'res-uuid-' . $i,
                        'fileId' => 100 + $i,
                        'name' => 'Model_' . $i,
                        'type' => 'polygen',
                        'info' => '{"size":1024}',
                    ];
                }

                // Set the required field to empty string on the last mapping
                $targetIndex = $mappingCount - 1;
                $mappings[$targetIndex][$fieldToEmpty] = '';

                $importData['resourceFileMappings'] = $mappings;

                $this->expectException(BadRequestHttpException::class);
                $this->callValidateImportData($importData);
            });
    }

    // =========================================================================
    // Property 7: Combined — randomly remove a field from any section
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 7: 导入数据验证拒绝不完整数据
     * Validates: Requirements 9.2, 9.3, 9.4
     *
     * For any import data, if we randomly select one required field from any
     * section (verse, metas, or resourceFileMappings) and remove it, the
     * validation logic should reject the data with BadRequestHttpException.
     */
    public function testRejectsDataWithAnyRandomMissingRequiredField(): void
    {
        // All possible (section, field) pairs
        $allRequiredFields = [
            ['section' => 'verse', 'field' => 'name'],
            ['section' => 'verse', 'field' => 'data'],
            ['section' => 'verse', 'field' => 'version'],
            ['section' => 'verse', 'field' => 'uuid'],
            ['section' => 'metas', 'field' => 'title'],
            ['section' => 'metas', 'field' => 'uuid'],
            ['section' => 'resourceFileMappings', 'field' => 'originalUuid'],
            ['section' => 'resourceFileMappings', 'field' => 'fileId'],
            ['section' => 'resourceFileMappings', 'field' => 'name'],
            ['section' => 'resourceFileMappings', 'field' => 'type'],
            ['section' => 'resourceFileMappings', 'field' => 'info'],
        ];

        $this
            ->limitTo(25)
            ->forAll(
                Generators::elements($allRequiredFields)
            )
            ->then(function (array $target): void {
                $importData = self::buildValidImportData();

                $section = $target['section'];
                $field = $target['field'];

                // Remove the selected required field from the appropriate section
                switch ($section) {
                    case 'verse':
                        unset($importData['verse'][$field]);
                        break;
                    case 'metas':
                        unset($importData['metas'][0][$field]);
                        break;
                    case 'resourceFileMappings':
                        unset($importData['resourceFileMappings'][0][$field]);
                        break;
                }

                $thrown = false;
                try {
                    $this->callValidateImportData($importData);
                } catch (BadRequestHttpException $e) {
                    $thrown = true;
                    $this->assertStringContainsString(
                        $field,
                        $e->getMessage(),
                        "Error message should mention the missing field '{$field}'"
                    );
                }

                $this->assertTrue(
                    $thrown,
                    "Expected BadRequestHttpException for missing {$section}.{$field}"
                );
            });
    }
}
