<?php

namespace tests\unit\models;

use api\modules\v1\models\Space;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

final class SpaceTest extends TestCase
{
    private $originalDbComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%space}}', [
            'id' => 'pk',
            'name' => 'string not null',
            'user_id' => 'integer not null',
            'mesh_id' => 'integer not null',
            'image_id' => 'integer',
            'file_id' => 'integer',
            'created_at' => 'datetime',
            'data' => 'text',
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testDataArrayIsEncodedBeforeValidationAndDecodedForFields(): void
    {
        $space = new Space([
            'name' => 'SLAM Space',
            'user_id' => 42,
            'mesh_id' => 100,
            'data' => ['provider' => 'immersal', 'mapId' => 'map-001'],
        ]);

        $this->assertTrue($space->beforeValidate());
        $this->assertSame('{"provider":"immersal","mapId":"map-001"}', $space->data);

        $array = $space->toArray(['data']);
        $this->assertSame('immersal', $array['data']['provider']);
        $this->assertSame('map-001', $array['data']['mapId']);
    }

    public function testArSlamDataIsReducedBeforeStorage(): void
    {
        $space = new Space([
            'name' => 'SLAM Space',
            'user_id' => 42,
            'mesh_id' => 11543,
            'file_id' => 11544,
            'image_id' => 11545,
            'data' => [
                'source' => 'ar-slam-localization',
                'provider' => 'immersal',
                'zipMd5' => '56d97f43f244272abe5341851ae7d839',
                'zipName' => '143403-Ofc2.zip',
                'cosPrefix' => 'spaces/56d97f43f244272abe5341851ae7d839',
                'modelFileId' => 11543,
                'thumbnailFileId' => 11545,
                'screenshotKey' => 'spaces/56d97f43f244272abe5341851ae7d839.png',
                'localizationFileIds' => [11544],
                'primaryLocalizationFileId' => 11544,
                'files' => [
                    [
                        'fileId' => 11543,
                        'role' => 'model',
                        'url' => 'https://example.test/model.glb',
                    ],
                    [
                        'fileId' => 11544,
                        'role' => 'localization',
                        'url' => 'https://example.test/map.bytes',
                    ],
                ],
            ],
        ]);

        $this->assertTrue($space->beforeValidate());
        $storedData = json_decode($space->data, true);

        $this->assertSame([
            'source' => 'ar-slam-localization',
            'provider' => 'immersal',
            'zipMd5' => '56d97f43f244272abe5341851ae7d839',
            'zipName' => '143403-Ofc2.zip',
            'thumbnailFileId' => 11545,
        ], $storedData);
        $this->assertArrayNotHasKey('files', $storedData);
        $this->assertArrayNotHasKey('modelFileId', $storedData);
        $this->assertArrayNotHasKey('localizationFileIds', $storedData);
        $this->assertArrayNotHasKey('primaryLocalizationFileId', $storedData);
        $this->assertArrayNotHasKey('screenshotKey', $storedData);
        $this->assertArrayNotHasKey('cosPrefix', $storedData);
    }

    public function testAreaTargetScannerDataIsReducedBeforeStorage(): void
    {
        $space = new Space([
            'name' => 'uv_unwrap_fixed.zip',
            'user_id' => 42,
            'mesh_id' => 310,
            'file_id' => 311,
            'image_id' => 312,
            'data' => [
                'source' => 'ar-slam-localization',
                'provider' => 'area-target-scanner',
                'zipMd5' => 'area-target-content-md5',
                'zipName' => 'uv_unwrap_fixed.zip',
                'cosPrefix' => 'spaces/area-target-content-md5',
                'modelFileId' => 310,
                'primaryLocalizationFileId' => 311,
                'thumbnailFileId' => 312,
                'localizationFileIds' => [311],
                'manifestSummary' => [
                    'version' => '2.0',
                    'keyframeCount' => 18,
                    'rawKeys' => ['version', 'meshFile', 'featureDbFile'],
                ],
                'files' => [
                    [
                        'path' => 'mesh.glb',
                        'originalName' => 'optimized.glb',
                        'role' => 'model',
                    ],
                    [
                        'path' => 'file.zip',
                        'role' => 'localization',
                        'entries' => [
                            ['path' => 'manifest.json', 'role' => 'manifest'],
                            ['path' => 'features.db', 'role' => 'localization'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($space->beforeValidate());
        $storedData = json_decode($space->data, true);

        $this->assertSame([
            'source' => 'ar-slam-localization',
            'provider' => 'area-target-scanner',
            'zipMd5' => 'area-target-content-md5',
            'zipName' => 'uv_unwrap_fixed.zip',
            'thumbnailFileId' => 312,
        ], $storedData);
        $this->assertArrayNotHasKey('files', $storedData);
        $this->assertArrayNotHasKey('manifestSummary', $storedData);
        $this->assertArrayNotHasKey('modelFileId', $storedData);
        $this->assertArrayNotHasKey('localizationFileIds', $storedData);
        $this->assertArrayNotHasKey('primaryLocalizationFileId', $storedData);
        $this->assertArrayNotHasKey('cosPrefix', $storedData);
    }

    public function testFileIdIsAnIntegerAttribute(): void
    {
        $rules = (new Space())->rules();
        $integerRules = array_values(array_filter(
            $rules,
            static fn (array $rule): bool => $rule[1] === 'integer'
        ));

        $this->assertNotEmpty($integerRules);
        $this->assertContains('file_id', $integerRules[0][0]);
    }
}
