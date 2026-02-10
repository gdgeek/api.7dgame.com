<?php

namespace tests\unit\helpers;

use api\modules\v1\helpers\IdRemapper;
use Eris;
use Eris\Generators;
use PHPUnit\Framework\TestCase;

/**
 * IdRemapper Property-Based Tests
 *
 * Uses Eris (PHP QuickCheck) to verify correctness properties of IdRemapper::remap()
 * across randomly generated JSON data trees and replacement rules.
 *
 * @group scene-package
 * @group id-remapper
 * @group property-based-test
 */
class IdRemapperPropertyTest extends TestCase
{
    use Eris\TestTrait;

    // =========================================================================
    // Helper: Generate random JSON data trees with embedded target keys
    // =========================================================================

    /**
     * Build a random JSON-like nested array that contains some known key-value
     * pairs (meta_id / resource) alongside arbitrary "noise" keys.
     *
     * @param array $embeddedPairs  e.g. [['key' => 'meta_id', 'value' => 42], ...]
     * @param int   $depth          remaining nesting depth
     * @return array
     */
    private static function buildRandomTree(array $embeddedPairs, int $depth, int $extraKeys = 3): array
    {
        $node = [];

        // Add some noise keys that should NOT be touched by remap
        for ($i = 0; $i < $extraKeys; $i++) {
            $noiseKey = 'noise_' . $i;
            $node[$noiseKey] = 'unchanged_' . $i;
        }

        // Embed the target key-value pairs at this level
        foreach ($embeddedPairs as $pair) {
            $node[$pair['key']] = $pair['value'];
        }

        // Optionally nest deeper
        if ($depth > 0) {
            $node['children'] = self::buildRandomTree($embeddedPairs, $depth - 1, $extraKeys);
        }

        return $node;
    }

    /**
     * Recursively collect all values for a given key in a nested array.
     *
     * @param array  $data
     * @param string $key
     * @return array  list of values found
     */
    private static function collectValues(array $data, string $key): array
    {
        $found = [];
        foreach ($data as $k => $v) {
            if (is_string($k) && $k === $key && !is_array($v)) {
                $found[] = $v;
            }
            if (is_array($v)) {
                $found = array_merge($found, self::collectValues($v, $key));
            }
        }
        return $found;
    }

    /**
     * Recursively collect all key-value pairs in a nested array (non-array values only).
     *
     * @param array $data
     * @return array  list of ['key' => ..., 'value' => ...]
     */
    private static function collectAllKeyValues(array $data): array
    {
        $pairs = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $pairs = array_merge($pairs, self::collectAllKeyValues($v));
            } else {
                $pairs[] = ['key' => $k, 'value' => $v];
            }
        }
        return $pairs;
    }

    // =========================================================================
    // Property 4: ID 重映射替换正确性
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 4: ID 重映射替换正确性
     * Validates: Requirements 6.1, 6.2, 6.3, 6.4
     *
     * For any JSON data tree and replacement rules (key name + old-to-new mapping),
     * after IdRemapper::remap(), all matching key fields should be replaced with
     * the new values from the map, and non-matching fields should remain unchanged.
     */
    public function testIdRemapperReplacementCorrectness(): void
    {
        $this
            ->limitTo(100)
            ->forAll(
                Generators::choose(1, 1000),   // oldMetaId
                Generators::choose(1001, 2000), // newMetaId
                Generators::choose(1, 500),     // oldResourceId
                Generators::choose(501, 1000),  // newResourceId
                Generators::choose(0, 3),       // nesting depth
                Generators::choose(1, 4)        // extra noise keys per level
            )
            ->then(function (
                int $oldMetaId,
                int $newMetaId,
                int $oldResourceId,
                int $newResourceId,
                int $depth,
                int $extraKeys
            ): void {
                // --- Arrange ---
                $embeddedPairs = [
                    ['key' => 'meta_id', 'value' => $oldMetaId],
                    ['key' => 'resource', 'value' => $oldResourceId],
                ];

                $data = self::buildRandomTree($embeddedPairs, $depth, $extraKeys);

                $replacements = [
                    ['key' => 'meta_id', 'map' => [$oldMetaId => $newMetaId], 'numericOnly' => false],
                    ['key' => 'resource', 'map' => [$oldResourceId => $newResourceId], 'numericOnly' => true],
                ];

                // --- Act ---
                $result = IdRemapper::remap($data, $replacements);

                // --- Assert: all meta_id values should be the new value ---
                $metaIdValues = self::collectValues($result, 'meta_id');
                foreach ($metaIdValues as $val) {
                    $this->assertSame(
                        $newMetaId,
                        $val,
                        "meta_id should be replaced: expected {$newMetaId}, got " . var_export($val, true)
                    );
                }

                // --- Assert: all numeric resource values should be the new value ---
                $resourceValues = self::collectValues($result, 'resource');
                foreach ($resourceValues as $val) {
                    $this->assertSame(
                        $newResourceId,
                        $val,
                        "resource should be replaced: expected {$newResourceId}, got " . var_export($val, true)
                    );
                }

                // --- Assert: noise keys remain unchanged ---
                $allPairs = self::collectAllKeyValues($result);
                foreach ($allPairs as $pair) {
                    if (str_starts_with((string)$pair['key'], 'noise_')) {
                        $this->assertStringStartsWith(
                            'unchanged_',
                            (string)$pair['value'],
                            "Noise key '{$pair['key']}' should not be modified"
                        );
                    }
                }
            });
    }

    /**
     * Feature: scene-package-api, Property 4: ID 重映射替换正确性 (numericOnly guard)
     * Validates: Requirements 6.2, 6.3, 6.4
     *
     * When numericOnly is true, string values for the matching key should NOT be
     * replaced, even if the string representation exists in the map.
     */
    public function testIdRemapperNumericOnlyGuard(): void
    {
        $this
            ->limitTo(100)
            ->forAll(
                Generators::choose(1, 1000),   // oldResourceId (numeric)
                Generators::choose(1001, 2000), // newResourceId
                Generators::choose(0, 3)        // depth
            )
            ->then(function (int $oldResourceId, int $newResourceId, int $depth): void {
                // Build a tree where 'resource' has a STRING value (not numeric)
                $stringValue = 'resource_string_' . $oldResourceId;
                $embeddedPairs = [
                    ['key' => 'resource', 'value' => $stringValue],
                ];

                $data = self::buildRandomTree($embeddedPairs, $depth, 2);

                $replacements = [
                    [
                        'key' => 'resource',
                        'map' => [$stringValue => $newResourceId, $oldResourceId => $newResourceId],
                        'numericOnly' => true,
                    ],
                ];

                $result = IdRemapper::remap($data, $replacements);

                // String resource values should NOT be replaced when numericOnly is true
                $resourceValues = self::collectValues($result, 'resource');
                foreach ($resourceValues as $val) {
                    $this->assertSame(
                        $stringValue,
                        $val,
                        "String resource value should NOT be replaced when numericOnly=true"
                    );
                }
            });
    }

    /**
     * Feature: scene-package-api, Property 4: ID 重映射替换正确性 (multiple rules)
     * Validates: Requirements 6.1, 6.2, 6.3, 6.4
     *
     * Multiple replacement rules applied simultaneously should each replace
     * their respective keys independently without interfering with each other.
     */
    public function testIdRemapperMultipleRulesIndependence(): void
    {
        $this
            ->limitTo(100)
            ->forAll(
                Generators::choose(1, 500),     // oldMetaId1
                Generators::choose(501, 1000),  // newMetaId1
                Generators::choose(1, 500),     // oldMetaId2
                Generators::choose(501, 1000),  // newMetaId2
                Generators::choose(1, 500),     // oldResId
                Generators::choose(501, 1000),  // newResId
                Generators::choose(1, 3)        // depth
            )
            ->then(function (
                int $oldMetaId1,
                int $newMetaId1,
                int $oldMetaId2,
                int $newMetaId2,
                int $oldResId,
                int $newResId,
                int $depth
            ): void {
                // Build a tree with meta_id and resource at multiple levels
                $data = [
                    'meta_id' => $oldMetaId1,
                    'resource' => $oldResId,
                    'nested' => [
                        'meta_id' => $oldMetaId2,
                        'resource' => $oldResId,
                        'other_key' => 'should_stay',
                    ],
                ];

                // Add deeper nesting
                if ($depth > 0) {
                    $data['deep'] = self::buildRandomTree(
                        [['key' => 'meta_id', 'value' => $oldMetaId1], ['key' => 'resource', 'value' => $oldResId]],
                        $depth - 1,
                        2
                    );
                }

                $replacements = [
                    ['key' => 'meta_id', 'map' => [$oldMetaId1 => $newMetaId1, $oldMetaId2 => $newMetaId2], 'numericOnly' => false],
                    ['key' => 'resource', 'map' => [$oldResId => $newResId], 'numericOnly' => true],
                ];

                $result = IdRemapper::remap($data, $replacements);

                // Verify resource replacements
                $resourceValues = self::collectValues($result, 'resource');
                foreach ($resourceValues as $val) {
                    $this->assertSame(
                        $newResId,
                        $val,
                        "All resource values should be replaced"
                    );
                }

                // Verify meta_id replacements - all should be mapped to new values
                $metaIdValues = self::collectValues($result, 'meta_id');
                foreach ($metaIdValues as $val) {
                    $this->assertTrue(
                        $val === $newMetaId1 || $val === $newMetaId2,
                        "meta_id should be one of the new values, got: " . var_export($val, true)
                    );
                }

                // Verify non-matching keys are untouched
                $this->assertSame('should_stay', $result['nested']['other_key']);
            });
    }

    // =========================================================================
    // Property 5: ID 重映射完整性不变量
    // =========================================================================

    /**
     * Feature: scene-package-api, Property 5: ID 重映射完整性不变量
     * Validates: Requirements 6.5
     *
     * For any JSON data tree and a COMPLETE ID mapping table (covering all old IDs
     * in the data), after IdRemapper::remap(), no old ID values should remain in
     * the result for the matched keys.
     */
    public function testIdRemapperCompletenessInvariant(): void
    {
        $this
            ->limitTo(100)
            ->forAll(
                Generators::choose(2, 6),       // number of distinct old meta IDs
                Generators::choose(2, 6),       // number of distinct old resource IDs
                Generators::choose(0, 3),       // nesting depth
                Generators::choose(1000, 2000)  // offset for new IDs (ensures no overlap)
            )
            ->then(function (
                int $numMetaIds,
                int $numResourceIds,
                int $depth,
                int $newIdOffset
            ): void {
                // --- Generate distinct old IDs ---
                $oldMetaIds = range(1, $numMetaIds);
                $oldResourceIds = range(1, $numResourceIds);

                // --- Build complete mapping tables (covering ALL old IDs) ---
                $metaMap = [];
                foreach ($oldMetaIds as $oldId) {
                    $metaMap[$oldId] = $oldId + $newIdOffset;
                }

                $resourceMap = [];
                foreach ($oldResourceIds as $oldId) {
                    $resourceMap[$oldId] = $oldId + $newIdOffset + 5000;
                }

                // --- Build a data tree that uses these old IDs ---
                $data = self::buildTreeWithIds($oldMetaIds, $oldResourceIds, $depth);

                $replacements = [
                    ['key' => 'meta_id', 'map' => $metaMap, 'numericOnly' => false],
                    ['key' => 'resource', 'map' => $resourceMap, 'numericOnly' => true],
                ];

                // --- Act ---
                $result = IdRemapper::remap($data, $replacements);

                // --- Assert: no old meta_id values remain ---
                $resultMetaIds = self::collectValues($result, 'meta_id');
                foreach ($resultMetaIds as $val) {
                    $this->assertFalse(
                        in_array($val, $oldMetaIds, true),
                        "Old meta_id {$val} should not remain after remap with complete mapping"
                    );
                    // Additionally verify it IS one of the new values
                    $this->assertTrue(
                        in_array($val, array_values($metaMap), true),
                        "meta_id value {$val} should be in the new ID set"
                    );
                }

                // --- Assert: no old resource values remain ---
                $resultResourceIds = self::collectValues($result, 'resource');
                foreach ($resultResourceIds as $val) {
                    $this->assertFalse(
                        in_array($val, $oldResourceIds, true),
                        "Old resource ID {$val} should not remain after remap with complete mapping"
                    );
                    $this->assertTrue(
                        in_array($val, array_values($resourceMap), true),
                        "resource value {$val} should be in the new ID set"
                    );
                }
            });
    }

    /**
     * Feature: scene-package-api, Property 5: ID 重映射完整性不变量 (complex tree)
     * Validates: Requirements 6.5
     *
     * Even with complex nested structures containing arrays-of-objects (like
     * verse.data.children.modules), a complete mapping should leave no old IDs.
     */
    public function testIdRemapperCompletenessWithComplexTree(): void
    {
        $this
            ->limitTo(100)
            ->forAll(
                Generators::choose(1, 5),       // number of modules
                Generators::choose(1, 3),       // number of meta IDs
                Generators::choose(1, 3),       // number of resource IDs
                Generators::choose(1000, 5000)  // new ID offset
            )
            ->then(function (
                int $numModules,
                int $numMetaIds,
                int $numResourceIds,
                int $newIdOffset
            ): void {
                $oldMetaIds = range(1, $numMetaIds);
                $oldResourceIds = range(100, 100 + $numResourceIds - 1);

                // Build complete maps
                $metaMap = [];
                foreach ($oldMetaIds as $id) {
                    $metaMap[$id] = $id + $newIdOffset;
                }
                $resourceMap = [];
                foreach ($oldResourceIds as $id) {
                    $resourceMap[$id] = $id + $newIdOffset;
                }

                // Build a realistic verse.data-like structure
                $modules = [];
                for ($i = 0; $i < $numModules; $i++) {
                    $metaId = $oldMetaIds[$i % $numMetaIds];
                    $resId = $oldResourceIds[$i % $numResourceIds];
                    $modules[] = [
                        'type' => 'Module',
                        'meta_id' => $metaId,
                        'resource' => $resId,
                        'properties' => [
                            'name' => 'module_' . $i,
                            'meta_id' => $metaId,
                            'nested_resource' => [
                                'resource' => $resId,
                                'label' => 'some_label',
                            ],
                        ],
                    ];
                }

                $data = [
                    'type' => 'Verse',
                    'children' => [
                        'modules' => $modules,
                    ],
                ];

                $replacements = [
                    ['key' => 'meta_id', 'map' => $metaMap, 'numericOnly' => false],
                    ['key' => 'resource', 'map' => $resourceMap, 'numericOnly' => true],
                ];

                $result = IdRemapper::remap($data, $replacements);

                // Verify completeness: no old IDs remain
                $allMetaIds = self::collectValues($result, 'meta_id');
                foreach ($allMetaIds as $val) {
                    $this->assertFalse(
                        in_array($val, $oldMetaIds, true),
                        "Old meta_id {$val} should not remain in complex tree"
                    );
                }

                $allResourceIds = self::collectValues($result, 'resource');
                foreach ($allResourceIds as $val) {
                    $this->assertFalse(
                        in_array($val, $oldResourceIds, true),
                        "Old resource ID {$val} should not remain in complex tree"
                    );
                }

                // Verify non-target fields are preserved
                $this->assertSame('Verse', $result['type']);
                $this->assertCount($numModules, $result['children']['modules']);
                foreach ($result['children']['modules'] as $i => $module) {
                    $this->assertSame('Module', $module['type']);
                    $this->assertSame('module_' . $i, $module['properties']['name']);
                    $this->assertSame('some_label', $module['properties']['nested_resource']['label']);
                }
            });
    }

    // =========================================================================
    // Helper: Build a tree that uses specific old IDs at multiple levels
    // =========================================================================

    /**
     * Build a nested data tree that distributes the given old IDs across
     * multiple levels, simulating realistic verse/meta data structures.
     */
    private static function buildTreeWithIds(array $oldMetaIds, array $oldResourceIds, int $depth): array
    {
        $metaIdx = 0;
        $resIdx = 0;
        $metaCount = count($oldMetaIds);
        $resCount = count($oldResourceIds);

        $node = [
            'type' => 'Root',
            'meta_id' => $oldMetaIds[$metaIdx % $metaCount],
            'resource' => $oldResourceIds[$resIdx % $resCount],
            'title' => 'root_node',
        ];

        $current = &$node;
        for ($d = 0; $d < $depth; $d++) {
            $metaIdx++;
            $resIdx++;
            $current['child'] = [
                'type' => 'Level_' . ($d + 1),
                'meta_id' => $oldMetaIds[$metaIdx % $metaCount],
                'resource' => $oldResourceIds[$resIdx % $resCount],
                'label' => 'level_' . ($d + 1),
            ];
            $current = &$current['child'];
        }

        return $node;
    }
}
