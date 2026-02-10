<?php

namespace tests\unit\services;

use Eris;
use Eris\Generators;
use PHPUnit\Framework\TestCase;

/**
 * ScenePackageService Property-Based Tests
 *
 * Uses Eris (PHP QuickCheck) to verify ZIP serialization round-trip consistency
 * for Scene_Data_Tree structures.
 *
 * @group scene-package
 * @group zip-roundtrip
 * @group property-based-test
 */
class ScenePackagePropertyTest extends TestCase
{
    use Eris\TestTrait;

    /**
     * @var string[] Temp files to clean up after each test iteration
     */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        $this->tempFiles = [];
        parent::tearDown();
    }

    // =========================================================================
    // Helper: Generate random Scene_Data_Tree structures
    // =========================================================================

    /**
     * Build a random Scene_Data_Tree-like data structure.
     *
     * @param int    $numMetas      Number of metas to generate
     * @param int    $numResources  Number of resources to generate
     * @param int    $verseId       Verse ID
     * @param string $verseName     Verse name
     * @param string $verseDesc     Verse description
     * @param int    $verseVersion  Verse version
     * @param string $verseUuid     Verse UUID
     * @return array Scene_Data_Tree structure
     */
    private static function buildRandomSceneDataTree(
        int    $numMetas,
        int    $numResources,
        int    $verseId,
        string $verseName,
        string $verseDesc,
        int    $verseVersion,
        string $verseUuid
    ): array {
        // Build verse
        $verse = [
            'id' => $verseId,
            'author_id' => 1,
            'name' => $verseName,
            'description' => $verseDesc,
            'info' => null,
            'data' => [
                'type' => 'Verse',
                'children' => [
                    'modules' => [],
                ],
            ],
            'version' => $verseVersion,
            'uuid' => $verseUuid,
            'editable' => true,
            'viewable' => true,
            'verseRelease' => null,
            'image' => null,
            'verseCode' => [
                'blockly' => '<xml>' . $verseName . '</xml>',
                'lua' => 'print("' . $verseName . '")',
                'js' => 'console.log("' . $verseName . '")',
            ],
        ];

        // Build metas
        $metas = [];
        for ($i = 0; $i < $numMetas; $i++) {
            $metaId = 100 + $i;
            $metas[] = [
                'id' => $metaId,
                'author_id' => 1,
                'uuid' => 'meta-uuid-' . $i . '-' . $verseUuid,
                'title' => 'Meta_' . $i . '_' . $verseName,
                'data' => [
                    'type' => 'MetaRoot',
                    'children' => [
                        'properties' => [
                            'name' => 'meta_prop_' . $i,
                            'value' => $i * 10,
                        ],
                    ],
                ],
                'events' => [
                    'inputs' => [
                        ['name' => 'input_' . $i, 'type' => 'trigger'],
                    ],
                    'outputs' => [
                        ['name' => 'output_' . $i, 'type' => 'action'],
                    ],
                ],
                'image_id' => null,
                'image' => null,
                'prefab' => 0,
                'resources' => [],
                'editable' => true,
                'viewable' => true,
                'metaCode' => [
                    'blockly' => '<xml>meta_' . $i . '</xml>',
                    'lua' => 'print("meta_' . $i . '")',
                ],
            ];
        }

        // Build resources
        $resources = [];
        for ($i = 0; $i < $numResources; $i++) {
            $resourceId = 200 + $i;
            $resources[] = [
                'id' => $resourceId,
                'uuid' => 'res-uuid-' . $i . '-' . $verseUuid,
                'name' => 'Resource_' . $i . '_' . $verseName,
                'type' => ($i % 3 === 0) ? 'polygen' : (($i % 3 === 1) ? 'image' : 'audio'),
                'info' => json_encode(['size' => 1024 * ($i + 1), 'format' => 'glb']),
                'created_at' => '2025-01-15T08:00:00Z',
                'file' => [
                    'id' => 300 + $i,
                    'md5' => md5('file_' . $i . '_' . $verseUuid),
                    'type' => 'model/gltf-binary',
                    'url' => 'https://example.com/files/resource_' . $i . '.glb',
                    'filename' => 'resource_' . $i . '.glb',
                    'size' => 524288 * ($i + 1),
                    'key' => 'res-uuid-' . $i . '.glb',
                ],
            ];
        }

        // Build metaResourceLinks
        $metaResourceLinks = [];
        for ($m = 0; $m < $numMetas; $m++) {
            for ($r = 0; $r < $numResources; $r++) {
                // Link each meta to each resource (for simplicity)
                if (($m + $r) % 2 === 0) {
                    $metaResourceLinks[] = [
                        'meta_id' => 100 + $m,
                        'resource_id' => 200 + $r,
                    ];
                }
            }
        }

        return [
            'verse' => $verse,
            'metas' => $metas,
            'resources' => $resources,
            'metaResourceLinks' => $metaResourceLinks,
        ];
    }

    /**
     * Create a ZIP file containing scene.json with the given data.
     *
     * @param array $sceneData The Scene_Data_Tree data
     * @return string Path to the created temp ZIP file
     */
    private function createZipWithSceneJson(array $sceneData): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'scene_pbt_');
        $this->tempFiles[] = $tmpFile;

        $zip = new \ZipArchive();
        $openResult = $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $this->assertTrue($openResult === true, 'Failed to create ZIP file');

        $json = json_encode($sceneData, JSON_UNESCAPED_UNICODE);
        $zip->addFromString('scene.json', $json);
        $zip->close();

        return $tmpFile;
    }

    /**
     * Extract scene.json from a ZIP file and return the decoded data.
     *
     * @param string $zipPath Path to the ZIP file
     * @return array Decoded scene data
     */
    private function extractSceneJsonFromZip(string $zipPath): array
    {
        $zip = new \ZipArchive();
        $openResult = $zip->open($zipPath);
        $this->assertTrue($openResult === true, 'Failed to open ZIP file for reading');

        $sceneJson = $zip->getFromName('scene.json');
        $zip->close();

        $this->assertNotFalse($sceneJson, 'scene.json not found in ZIP');

        $data = json_decode($sceneJson, true);
        $this->assertNotNull($data, 'Failed to decode scene.json from ZIP');

        return $data;
    }

    // =========================================================================
    // Property 2: ZIP 序列化往返一致性
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 2: ZIP 序列化往返一致性
     * Validates: Requirements 2.2
     *
     * For any valid Scene_Data_Tree data, serializing it to JSON, storing it as
     * `scene.json` inside a ZIP file, then extracting `scene.json` from the ZIP
     * and deserializing it, should produce data equivalent to the original.
     */
    public function testZipSerializationRoundTripConsistency(): void
    {
        $this
            ->limitTo(25)
            ->forAll(
                Generators::choose(0, 5),       // numMetas
                Generators::choose(0, 5),       // numResources
                Generators::choose(1, 9999),    // verseId
                Generators::suchThat(
                    function (string $s): bool { return strlen($s) > 0; },
                    Generators::string()
                ),                              // verseName (non-empty)
                Generators::string(),           // verseDesc
                Generators::choose(1, 100),     // verseVersion
                Generators::suchThat(
                    function (string $s): bool { return strlen($s) > 0; },
                    Generators::string()
                )                               // verseUuid (non-empty)
            )
            ->then(function (
                int    $numMetas,
                int    $numResources,
                int    $verseId,
                string $verseName,
                string $verseDesc,
                int    $verseVersion,
                string $verseUuid
            ): void {
                // --- Arrange: build a random Scene_Data_Tree ---
                $originalData = self::buildRandomSceneDataTree(
                    $numMetas,
                    $numResources,
                    $verseId,
                    $verseName,
                    $verseDesc,
                    $verseVersion,
                    $verseUuid
                );

                // --- Act: serialize to ZIP and deserialize back ---
                $zipPath = $this->createZipWithSceneJson($originalData);
                $roundTrippedData = $this->extractSceneJsonFromZip($zipPath);

                // --- Assert: round-tripped data equals original ---
                $this->assertEquals(
                    $originalData,
                    $roundTrippedData,
                    'ZIP round-trip should preserve Scene_Data_Tree data exactly'
                );

                // --- Additional structural assertions ---
                // Verify top-level keys are preserved
                $this->assertArrayHasKey('verse', $roundTrippedData);
                $this->assertArrayHasKey('metas', $roundTrippedData);
                $this->assertArrayHasKey('resources', $roundTrippedData);
                $this->assertArrayHasKey('metaResourceLinks', $roundTrippedData);

                // Verify counts are preserved
                $this->assertCount(
                    count($originalData['metas']),
                    $roundTrippedData['metas'],
                    'Number of metas should be preserved after ZIP round-trip'
                );
                $this->assertCount(
                    count($originalData['resources']),
                    $roundTrippedData['resources'],
                    'Number of resources should be preserved after ZIP round-trip'
                );
                $this->assertCount(
                    count($originalData['metaResourceLinks']),
                    $roundTrippedData['metaResourceLinks'],
                    'Number of metaResourceLinks should be preserved after ZIP round-trip'
                );

                // Verify verse fields are preserved
                $this->assertSame(
                    $originalData['verse']['id'],
                    $roundTrippedData['verse']['id'],
                    'Verse ID should be preserved'
                );
                $this->assertSame(
                    $originalData['verse']['name'],
                    $roundTrippedData['verse']['name'],
                    'Verse name should be preserved'
                );
                $this->assertSame(
                    $originalData['verse']['uuid'],
                    $roundTrippedData['verse']['uuid'],
                    'Verse UUID should be preserved'
                );
                $this->assertSame(
                    $originalData['verse']['version'],
                    $roundTrippedData['verse']['version'],
                    'Verse version should be preserved'
                );
            });
    }

    /**
     * Feature: scene-package-api, Property 2: ZIP 序列化往返一致性
     * Validates: Requirements 2.2
     *
     * Verify that Unicode characters (Chinese, emoji, special chars) in
     * Scene_Data_Tree data survive the ZIP round-trip without corruption.
     */
    public function testZipRoundTripPreservesUnicodeContent(): void
    {
        $this
            ->limitTo(25)
            ->forAll(
                Generators::choose(0, 3),       // numMetas
                Generators::choose(0, 3),       // numResources
                Generators::choose(1, 9999),    // verseId
                Generators::elements([
                    '场景名称',
                    '测试场景_中文',
                    'Scene with émojis 🎮🎲',
                    '日本語テスト',
                    'Mixed 中英文 Test',
                    '特殊字符 <>&"\'',
                    "换行\n制表\t符号",
                    '数学符号 ∑∏∫',
                ]),                              // verseName with Unicode
                Generators::elements([
                    '这是一个描述',
                    'Description with 中文',
                    '',
                    '包含特殊字符的描述 <script>alert(1)</script>',
                ]),                              // verseDesc
                Generators::choose(1, 100)      // verseVersion
            )
            ->then(function (
                int    $numMetas,
                int    $numResources,
                int    $verseId,
                string $verseName,
                string $verseDesc,
                int    $verseVersion
            ): void {
                $verseUuid = 'uuid-unicode-' . $verseId;

                $originalData = self::buildRandomSceneDataTree(
                    $numMetas,
                    $numResources,
                    $verseId,
                    $verseName,
                    $verseDesc,
                    $verseVersion,
                    $verseUuid
                );

                // Round-trip through ZIP
                $zipPath = $this->createZipWithSceneJson($originalData);
                $roundTrippedData = $this->extractSceneJsonFromZip($zipPath);

                // Assert exact equality (including Unicode)
                $this->assertEquals(
                    $originalData,
                    $roundTrippedData,
                    'ZIP round-trip should preserve Unicode content exactly'
                );

                // Specifically verify the Unicode name survived
                $this->assertSame(
                    $verseName,
                    $roundTrippedData['verse']['name'],
                    'Unicode verse name should be preserved after ZIP round-trip'
                );

                $this->assertSame(
                    $verseDesc,
                    $roundTrippedData['verse']['description'],
                    'Unicode verse description should be preserved after ZIP round-trip'
                );
            });
    }

    /**
     * Feature: scene-package-api, Property 2: ZIP 序列化往返一致性
     * Validates: Requirements 2.2
     *
     * Verify that the JSON encoding used in ZIP (JSON_UNESCAPED_UNICODE) produces
     * the same result as decoding and re-encoding, ensuring idempotency.
     */
    public function testZipRoundTripJsonEncodingIdempotency(): void
    {
        $this
            ->limitTo(25)
            ->forAll(
                Generators::choose(0, 5),       // numMetas
                Generators::choose(0, 5),       // numResources
                Generators::choose(1, 9999),    // verseId
                Generators::choose(1, 100)      // verseVersion
            )
            ->then(function (
                int $numMetas,
                int $numResources,
                int $verseId,
                int $verseVersion
            ): void {
                $originalData = self::buildRandomSceneDataTree(
                    $numMetas,
                    $numResources,
                    $verseId,
                    'TestScene_' . $verseId,
                    'Description for scene ' . $verseId,
                    $verseVersion,
                    'uuid-idempotent-' . $verseId
                );

                // First round-trip
                $zipPath1 = $this->createZipWithSceneJson($originalData);
                $data1 = $this->extractSceneJsonFromZip($zipPath1);

                // Second round-trip (from the result of the first)
                $zipPath2 = $this->createZipWithSceneJson($data1);
                $data2 = $this->extractSceneJsonFromZip($zipPath2);

                // Both round-trips should produce identical results
                $this->assertEquals(
                    $data1,
                    $data2,
                    'Double ZIP round-trip should be idempotent'
                );

                // And both should equal the original
                $this->assertEquals(
                    $originalData,
                    $data2,
                    'Double ZIP round-trip should still equal original data'
                );
            });
    }
}
