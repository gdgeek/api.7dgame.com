<?php

namespace tests\unit\helpers;

use api\modules\v1\helpers\IdRemapper;
use PHPUnit\Framework\TestCase;

/**
 * IdRemapper Unit Tests
 *
 * Tests the IdRemapper utility class for recursive JSON data tree ID replacement.
 *
 * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5
 *
 * @group scene-package
 * @group id-remapper
 */
class IdRemapperTest extends TestCase
{
    // =========================================================================
    // meta_id replacement tests (Requirement 6.1)
    // =========================================================================

    /**
     * Test that meta_id values in verse.data are replaced with new IDs.
     * Requirement 6.1: Replace all key=meta_id fields with new Meta IDs
     */
    public function testReplacesMetaIdInVerseData(): void
    {
        $data = [
            'type' => 'Verse',
            'children' => [
                'modules' => [
                    [
                        'type' => 'Module',
                        'meta_id' => 100,
                        'name' => 'TestModule',
                    ],
                    [
                        'type' => 'Module',
                        'meta_id' => 200,
                        'name' => 'AnotherModule',
                    ],
                ],
            ],
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [100 => 500, 200 => 600], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(500, $result['children']['modules'][0]['meta_id']);
        $this->assertEquals(600, $result['children']['modules'][1]['meta_id']);
    }

    /**
     * Test that meta_id replacement works with string values.
     * Requirement 6.1: meta_id can be any type (numericOnly is false)
     */
    public function testReplacesMetaIdWithStringValues(): void
    {
        $data = [
            'meta_id' => 'old-meta-uuid',
            'name' => 'Test',
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => ['old-meta-uuid' => 'new-meta-uuid'], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals('new-meta-uuid', $result['meta_id']);
    }

    // =========================================================================
    // resource replacement with numericOnly (Requirements 6.2, 6.3)
    // =========================================================================

    /**
     * Test that numeric resource values are replaced.
     * Requirement 6.2: Replace resource key with numeric values
     */
    public function testReplacesNumericResourceValues(): void
    {
        $data = [
            'type' => 'MetaRoot',
            'children' => [
                'entities' => [
                    [
                        'type' => 'polygen',
                        'parameters' => [
                            'resource' => 301,
                            'scale' => [1, 1, 1],
                        ],
                    ],
                ],
            ],
        ];

        $replacements = [
            ['key' => 'resource', 'map' => [301 => 701], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(701, $result['children']['entities'][0]['parameters']['resource']);
    }

    /**
     * Test that string resource values are NOT replaced when numericOnly is true.
     * Requirement 6.2: numericOnly=true means only replace numeric type values
     */
    public function testDoesNotReplaceStringResourceWhenNumericOnly(): void
    {
        $data = [
            'type' => 'MetaRoot',
            'parameters' => [
                'resource' => 'some-string-resource',
            ],
        ];

        $replacements = [
            ['key' => 'resource', 'map' => ['some-string-resource' => 'new-resource'], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals('some-string-resource', $result['parameters']['resource']);
    }

    /**
     * Test that string resource values ARE replaced when numericOnly is false.
     */
    public function testReplacesStringResourceWhenNumericOnlyFalse(): void
    {
        $data = [
            'parameters' => [
                'resource' => 'old-resource',
            ],
        ];

        $replacements = [
            ['key' => 'resource', 'map' => ['old-resource' => 'new-resource'], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals('new-resource', $result['parameters']['resource']);
    }

    // =========================================================================
    // Recursive/nested structure tests (Requirement 6.3, 6.4)
    // =========================================================================

    /**
     * Test recursive replacement in deeply nested JSON structures.
     * Requirement 6.3: Recursively traverse JSON tree for meta.data
     */
    public function testRecursiveReplacementInDeeplyNestedStructure(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => [
                            'resource' => 10,
                            'meta_id' => 20,
                        ],
                    ],
                ],
            ],
        ];

        $replacements = [
            ['key' => 'resource', 'map' => [10 => 110], 'numericOnly' => true],
            ['key' => 'meta_id', 'map' => [20 => 220], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(110, $result['level1']['level2']['level3']['level4']['resource']);
        $this->assertEquals(220, $result['level1']['level2']['level3']['level4']['meta_id']);
    }

    /**
     * Test replacement in meta.events data (same logic as meta.data).
     * Requirement 6.4: events data uses same resource ID replacement logic
     */
    public function testReplacementInEventsData(): void
    {
        $events = [
            'inputs' => [
                [
                    'type' => 'trigger',
                    'resource' => 50,
                    'action' => 'play',
                ],
            ],
            'outputs' => [
                [
                    'type' => 'response',
                    'resource' => 60,
                ],
            ],
        ];

        $replacements = [
            ['key' => 'resource', 'map' => [50 => 150, 60 => 160], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($events, $replacements);

        $this->assertEquals(150, $result['inputs'][0]['resource']);
        $this->assertEquals(160, $result['outputs'][0]['resource']);
    }

    /**
     * Test multiple replacement rules applied simultaneously.
     */
    public function testMultipleReplacementRulesSimultaneously(): void
    {
        $data = [
            'meta_id' => 100,
            'children' => [
                [
                    'meta_id' => 200,
                    'resource' => 300,
                ],
            ],
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [100 => 500, 200 => 600], 'numericOnly' => false],
            ['key' => 'resource', 'map' => [300 => 700], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(500, $result['meta_id']);
        $this->assertEquals(600, $result['children'][0]['meta_id']);
        $this->assertEquals(700, $result['children'][0]['resource']);
    }

    // =========================================================================
    // Non-matching values remain unchanged (Requirement 6.5)
    // =========================================================================

    /**
     * Test that values not in the map remain unchanged.
     * Requirement 6.5: Only values in the map are replaced
     */
    public function testValuesNotInMapRemainUnchanged(): void
    {
        $data = [
            'meta_id' => 999,
            'name' => 'Test',
            'resource' => 888,
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [100 => 500], 'numericOnly' => false],
            ['key' => 'resource', 'map' => [300 => 700], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(999, $result['meta_id']);
        $this->assertEquals(888, $result['resource']);
        $this->assertEquals('Test', $result['name']);
    }

    /**
     * Test that non-matching keys are not affected.
     */
    public function testNonMatchingKeysAreNotAffected(): void
    {
        $data = [
            'id' => 100,
            'name' => 'Test',
            'type' => 'Verse',
            'version' => 3,
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [100 => 500], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(100, $result['id']);
        $this->assertEquals('Test', $result['name']);
        $this->assertEquals('Verse', $result['type']);
        $this->assertEquals(3, $result['version']);
    }

    // =========================================================================
    // Edge cases and boundary conditions
    // =========================================================================

    /**
     * Test with empty data array.
     */
    public function testEmptyDataArray(): void
    {
        $result = IdRemapper::remap([], [
            ['key' => 'meta_id', 'map' => [1 => 2], 'numericOnly' => false],
        ]);

        $this->assertEquals([], $result);
    }

    /**
     * Test with empty replacements array.
     */
    public function testEmptyReplacementsArray(): void
    {
        $data = ['meta_id' => 100, 'name' => 'Test'];

        $result = IdRemapper::remap($data, []);

        $this->assertEquals($data, $result);
    }

    /**
     * Test with null data input.
     */
    public function testNullDataInput(): void
    {
        $result = IdRemapper::remap(null, [
            ['key' => 'meta_id', 'map' => [1 => 2], 'numericOnly' => false],
        ]);

        $this->assertNull($result);
    }

    /**
     * Test with scalar data input (string).
     */
    public function testScalarStringDataInput(): void
    {
        $result = IdRemapper::remap('hello', [
            ['key' => 'meta_id', 'map' => [1 => 2], 'numericOnly' => false],
        ]);

        $this->assertEquals('hello', $result);
    }

    /**
     * Test with scalar data input (integer).
     */
    public function testScalarIntDataInput(): void
    {
        $result = IdRemapper::remap(42, [
            ['key' => 'meta_id', 'map' => [1 => 2], 'numericOnly' => false],
        ]);

        $this->assertEquals(42, $result);
    }

    /**
     * Test with empty map in replacement rule.
     */
    public function testEmptyMapInReplacementRule(): void
    {
        $data = ['meta_id' => 100];

        $result = IdRemapper::remap($data, [
            ['key' => 'meta_id', 'map' => [], 'numericOnly' => false],
        ]);

        $this->assertEquals(100, $result['meta_id']);
    }

    /**
     * Test that numericOnly defaults to false when not specified.
     */
    public function testNumericOnlyDefaultsToFalse(): void
    {
        $data = ['resource' => 'string-value'];

        $result = IdRemapper::remap($data, [
            ['key' => 'resource', 'map' => ['string-value' => 'new-value']],
        ]);

        $this->assertEquals('new-value', $result['resource']);
    }

    /**
     * Test with numeric indexed arrays (sequential arrays).
     */
    public function testNumericIndexedArrays(): void
    {
        $data = [
            ['meta_id' => 1, 'name' => 'A'],
            ['meta_id' => 2, 'name' => 'B'],
            ['meta_id' => 3, 'name' => 'C'],
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [1 => 10, 2 => 20, 3 => 30], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        $this->assertEquals(10, $result[0]['meta_id']);
        $this->assertEquals(20, $result[1]['meta_id']);
        $this->assertEquals(30, $result[2]['meta_id']);
    }

    /**
     * Test with float resource values and numericOnly=true.
     * Float values are considered numeric, so they pass the numericOnly check.
     * However, if the float value is not in the map, it remains unchanged.
     */
    public function testFloatResourceValueWithNumericOnly(): void
    {
        $data = ['resource' => 3.14];

        // Float values pass the numericOnly check (is_float returns true),
        // but since PHP array keys cast floats to ints, we test that
        // a float value not in the map remains unchanged.
        $replacements = [
            ['key' => 'resource', 'map' => [100 => 200], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        // 3.14 is not in the map, so it stays unchanged
        $this->assertEquals(3.14, $result['resource']);
    }

    /**
     * Test with null value for a matching key.
     */
    public function testNullValueForMatchingKey(): void
    {
        $data = ['meta_id' => null];

        $replacements = [
            ['key' => 'meta_id', 'map' => [100 => 200], 'numericOnly' => false],
        ];

        $result = IdRemapper::remap($data, $replacements);

        // null is not in the map, so it should remain null
        $this->assertNull($result['meta_id']);
    }

    /**
     * Test with boolean value for a matching key with numericOnly=true.
     */
    public function testBooleanValueWithNumericOnly(): void
    {
        $data = ['resource' => true];

        $replacements = [
            ['key' => 'resource', 'map' => [1 => 100], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($data, $replacements);

        // boolean is not numeric (is_int/is_float), so should not be replaced
        $this->assertTrue($result['resource']);
    }

    /**
     * Test realistic verse.data structure with mixed meta_id and resource replacements.
     */
    public function testRealisticVerseDataStructure(): void
    {
        $verseData = [
            'type' => 'Verse',
            'children' => [
                'modules' => [
                    [
                        'type' => 'Module',
                        'meta_id' => 101,
                        'children' => [
                            'entities' => [
                                [
                                    'type' => 'polygen',
                                    'meta_id' => 102,
                                    'parameters' => [
                                        'resource' => 201,
                                        'position' => [0, 0, 0],
                                    ],
                                ],
                                [
                                    'type' => 'picture',
                                    'meta_id' => 103,
                                    'parameters' => [
                                        'resource' => 202,
                                        'width' => 100,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'addons' => [
                    [
                        'type' => 'ImageTarget',
                        'parameters' => [
                            'resource' => 'texture-path.png',
                            'picture' => 203,
                        ],
                    ],
                ],
            ],
        ];

        $replacements = [
            ['key' => 'meta_id', 'map' => [101 => 501, 102 => 502, 103 => 503], 'numericOnly' => false],
            ['key' => 'resource', 'map' => [201 => 601, 202 => 602], 'numericOnly' => true],
        ];

        $result = IdRemapper::remap($verseData, $replacements);

        // meta_id replacements
        $this->assertEquals(501, $result['children']['modules'][0]['meta_id']);
        $this->assertEquals(502, $result['children']['modules'][0]['children']['entities'][0]['meta_id']);
        $this->assertEquals(503, $result['children']['modules'][0]['children']['entities'][1]['meta_id']);

        // resource replacements (numeric only)
        $this->assertEquals(601, $result['children']['modules'][0]['children']['entities'][0]['parameters']['resource']);
        $this->assertEquals(602, $result['children']['modules'][0]['children']['entities'][1]['parameters']['resource']);

        // String resource should NOT be replaced (numericOnly=true)
        $this->assertEquals('texture-path.png', $result['children']['addons'][0]['parameters']['resource']);

        // Non-matching keys should be unchanged
        $this->assertEquals('Verse', $result['type']);
        $this->assertEquals([0, 0, 0], $result['children']['modules'][0]['children']['entities'][0]['parameters']['position']);
        $this->assertEquals(100, $result['children']['modules'][0]['children']['entities'][1]['parameters']['width']);
    }
}
