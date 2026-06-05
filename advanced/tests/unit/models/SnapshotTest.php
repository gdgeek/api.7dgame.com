<?php

namespace tests\unit\models;

use api\modules\v1\models\Snapshot;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\db\Schema;

final class SnapshotTest extends TestCase
{
    private $originalDbComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();

        Yii::$app->db->createCommand()->createTable('{{%verse}}', [
            'id' => 'pk',
            'author_id' => 'integer',
            'updater_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'name' => 'string not null',
            'info' => 'text',
            'image_id' => 'integer',
            'data' => 'text',
            'uuid' => 'string',
            'description' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%verse_code}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null',
            'blockly' => 'text',
            'lua' => 'text',
            'js' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%snapshot}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null',
            'uuid' => 'string',
            'code' => 'text',
            'data' => 'text',
            'metas' => 'text',
            'resources' => 'text',
            'space' => 'text',
            'created_at' => 'datetime',
            'created_by' => 'integer',
            'managers' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%file}}', [
            'id' => 'pk',
            'md5' => 'string',
            'type' => 'string',
            'url' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'filename' => 'string',
            'size' => 'integer',
            'key' => 'string',
        ])->execute();
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
        Yii::$app->db->createCommand()->createTable('{{%verse_space}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null unique',
            'space_id' => 'integer not null',
            'created_at' => 'datetime',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%meta}}', [
            'id' => 'pk',
            'author_id' => 'integer',
            'uuid' => 'string',
            'title' => 'string',
            'data' => 'text',
            'events' => 'text',
            'image_id' => 'integer',
            'prefab' => 'integer',
            'code' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%verse_meta}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null',
            'meta_id' => 'integer not null',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%resource}}', [
            'id' => 'pk',
            'name' => 'string',
            'type' => 'string',
            'author_id' => 'integer',
            'created_at' => 'datetime',
            'file_id' => 'integer',
            'image_id' => 'integer',
            'info' => 'text',
            'updater_id' => 'integer',
            'uuid' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%meta_resource}}', [
            'id' => 'pk',
            'meta_id' => 'integer not null',
            'resource_id' => 'integer not null',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%manager}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null',
            'type' => 'string not null',
            'data' => 'text',
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testCreateByIdExportsOnlySpaceTypeImageMeshAndFile(): void
    {
        $this->insertVerse(501);
        $this->insertFile(100, 'mesh.glb', 'mesh-key', 'model/gltf-binary');
        $this->insertFile(101, 'thumbnail.png', 'thumbnail-key', 'image/png');
        $this->insertFile(102, 'runtime.bytes', 'runtime-key', 'application/octet-stream');
        Yii::$app->db->createCommand()->insert('{{%space}}', [
            'id' => 701,
            'name' => 'SLAM Space',
            'user_id' => 42,
            'mesh_id' => 100,
            'image_id' => 101,
            'file_id' => 102,
            'data' => json_encode([
                'source' => 'ar-slam-localization',
                'provider' => 'immersal',
                'files' => [
                    [
                        'fileId' => 100,
                        'role' => 'model',
                        'url' => 'https://example.test/mesh.glb',
                    ],
                    [
                        'fileId' => 102,
                        'role' => 'localization',
                        'url' => 'https://example.test/runtime.bytes',
                    ],
                ],
            ]),
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%verse_space}}', [
            'verse_id' => 501,
            'space_id' => 701,
        ])->execute();

        $snapshot = Snapshot::CreateById(501);
        $array = $snapshot->toArray([], ['space']);

        $this->assertArrayHasKey('space', $array);
        $this->assertSame(['type', 'image', 'mesh', 'file'], array_keys($array['space']));
        $this->assertSame('immersal', $array['space']['type']);
        $this->assertSame(101, $array['space']['image']['id']);
        $this->assertSame('thumbnail.png', $array['space']['image']['filename']);
        $this->assertSame(100, $array['space']['mesh']['id']);
        $this->assertSame('mesh.glb', $array['space']['mesh']['filename']);
        $this->assertSame(102, $array['space']['file']['id']);
        $this->assertSame('runtime.bytes', $array['space']['file']['filename']);
        $this->assertArrayNotHasKey('name', $array['space']);
        $this->assertArrayNotHasKey('data', $array['space']);
        $this->assertArrayNotHasKey('thumbnail', $array['space']);
        $encodedSpace = json_encode($array['space'], JSON_UNESCAPED_SLASHES);
        $this->assertStringNotContainsString('files', $encodedSpace);
        $this->assertStringNotContainsString('modelFileId', $encodedSpace);
        $this->assertStringNotContainsString('primaryLocalizationFileId', $encodedSpace);
        $this->assertStringNotContainsString('zipMd5', $encodedSpace);
        $this->assertStringNotContainsString('zipName', $encodedSpace);
        $this->assertArrayNotHasKey('id', $array['space']);
        $this->assertArrayNotHasKey('mesh_id', $array['space']);
        $this->assertArrayNotHasKey('image_id', $array['space']);
        $this->assertArrayNotHasKey('file_id', $array['space']);

        $snapshot->detachBehaviors();
        $snapshot->created_at = '2026-05-05 13:30:00';
        $this->assertTrue($snapshot->save());
        $reloaded = Snapshot::findOne(['verse_id' => 501]);
        $this->assertNotNull($reloaded);
        $this->assertSame($array['space'], $reloaded->toArray([], ['space'])['space']);
    }

    public function testCreateByIdExportsNullSpaceWhenVerseHasNoBinding(): void
    {
        $this->insertVerse(502);

        $snapshot = Snapshot::CreateById(502);
        $array = $snapshot->toArray([], ['space']);

        $this->assertArrayHasKey('space', $array);
        $this->assertNull($array['space']);
    }

    public function testCreateByIdKeepsCollectionFieldsAsArraysAfterSave(): void
    {
        $this->insertSnapshotContractVerse(503);

        $snapshot = Snapshot::CreateById(503);
        $snapshot->detachBehaviors();
        $snapshot->created_at = '2026-05-05 13:30:00';
        $this->assertTrue($snapshot->save());

        $reloaded = Snapshot::findOne(['verse_id' => 503]);
        $this->assertNotNull($reloaded);
        $array = $reloaded->toArray([], ['metas', 'resources', 'managers']);

        $this->assertIsArray($array['metas']);
        $this->assertSame(801, $array['metas'][0]['id']);
        $this->assertIsArray($array['resources']);
        $this->assertSame(901, $array['resources'][0]['id']);
        $this->assertIsArray($array['managers']);
        $this->assertSame('score', $array['managers'][0]['type']);
    }

    public function testNativeJsonSnapshotColumnsAreNotDoubleEncodedBeforeSave(): void
    {
        $this->markSnapshotColumnsAsNativeJson(['metas', 'resources', 'space', 'managers']);

        $snapshot = new Snapshot(['verse_id' => 601]);
        $snapshot->metas = [
            [
                'id' => 801,
                'data' => ['type' => 'MetaRoot'],
            ],
        ];
        $snapshot->resources = [
            [
                'id' => 901,
                'file' => ['id' => 201],
            ],
        ];
        $snapshot->space = [
            'type' => 'immersal',
            'mesh' => ['id' => 301],
        ];
        $snapshot->managers = [
            [
                'type' => 'score',
                'data' => ['value' => 10],
            ],
        ];

        $this->assertTrue($snapshot->beforeValidate());
        $this->assertIsArray($snapshot->metas);
        $this->assertSame('MetaRoot', $snapshot->metas[0]['data']['type']);
        $this->assertIsArray($snapshot->resources);
        $this->assertSame(201, $snapshot->resources[0]['file']['id']);
        $this->assertIsArray($snapshot->space);
        $this->assertSame('immersal', $snapshot->space['type']);
        $this->assertIsArray($snapshot->managers);
        $this->assertSame(10, $snapshot->managers[0]['data']['value']);
    }

    public function testTakePhotoResponseContractKeepsStructuredSnapshotFields(): void
    {
        $this->insertSnapshotContractVerse(504);

        $snapshot = Snapshot::CreateById(504);
        $snapshot->detachBehaviors();
        $snapshot->created_at = '2026-05-05 13:30:00';
        $this->assertTrue($snapshot->save());

        $response = $snapshot->toArray([], Snapshot::TAKE_PHOTO_EXTRA_FIELDS);

        $this->assertEqualsCanonicalizing(Snapshot::TAKE_PHOTO_EXTRA_FIELDS, array_keys($response));
        $this->assertIsInt($response['id']);
        $this->assertSame('Verse 504', $response['name']);
        $this->assertSame('Snapshot test verse', $response['description']);
        $this->assertSame('verse-504', $response['uuid']);
        $this->assertIsString($response['code']);
        $this->assertIsString($response['data']);
        $this->assertJson($response['data']);

        $this->assertIsArray($response['metas']);
        $this->assertSame(801, $response['metas'][0]['id']);
        $this->assertSame('entity', $response['metas'][0]['type']);
        $this->assertIsString($response['metas'][0]['data']);
        $this->assertJson($response['metas'][0]['data']);
        $this->assertIsString($response['metas'][0]['events']);
        $this->assertJson($response['metas'][0]['events']);

        $this->assertIsArray($response['resources']);
        $this->assertSame(901, $response['resources'][0]['id']);
        $this->assertSame('polygen', $response['resources'][0]['type']);
        $this->assertSame(201, $response['resources'][0]['file']['id']);
        $this->assertSame(202, $response['resources'][0]['image']['id']);

        $this->assertIsArray($response['space']);
        $this->assertSame('immersal', $response['space']['type']);
        $this->assertSame(302, $response['space']['image']['id']);
        $this->assertSame(301, $response['space']['mesh']['id']);
        $this->assertSame(303, $response['space']['file']['id']);
        $this->assertArrayNotHasKey('managers', $response);
    }

    public function testServerSnapshotExpandContractSurvivesSaveAndJsonEncoding(): void
    {
        $this->insertSnapshotContractVerse(505);
        $serverSnapshotExpand = [
            'id',
            'name',
            'description',
            'data',
            'metas',
            'resources',
            'code',
            'uuid',
            'image',
            'managers',
            'verse_id',
        ];

        $snapshot = Snapshot::CreateById(505);
        $snapshot->detachBehaviors();
        $snapshot->created_at = '2026-05-05 13:30:00';
        $this->assertTrue($snapshot->save());

        $reloaded = Snapshot::findOne(['verse_id' => 505]);
        $this->assertNotNull($reloaded);
        $response = $reloaded->toArray([], $serverSnapshotExpand);
        $wireResponse = json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEqualsCanonicalizing($serverSnapshotExpand, array_keys($wireResponse));
        $this->assertSame(505, $wireResponse['verse_id']);
        $this->assertSame('Verse 505', $wireResponse['name']);
        $this->assertSame('Snapshot test verse', $wireResponse['description']);
        $this->assertSame('verse-505', $wireResponse['uuid']);
        $this->assertIsString($wireResponse['data']);
        $this->assertJson($wireResponse['data']);

        $this->assertIsArray($wireResponse['metas']);
        $this->assertSame(801, $wireResponse['metas'][0]['id']);
        $this->assertIsString($wireResponse['metas'][0]['data']);
        $this->assertJson($wireResponse['metas'][0]['data']);
        $this->assertIsString($wireResponse['metas'][0]['events']);
        $this->assertJson($wireResponse['metas'][0]['events']);

        $this->assertIsArray($wireResponse['resources']);
        $this->assertSame(901, $wireResponse['resources'][0]['id']);
        $this->assertSame(201, $wireResponse['resources'][0]['file']['id']);
        $this->assertSame(202, $wireResponse['resources'][0]['image']['id']);

        $this->assertIsArray($wireResponse['managers']);
        $this->assertSame('score', $wireResponse['managers'][0]['type']);
        $this->assertIsString($wireResponse['managers'][0]['data']);
        $this->assertJson($wireResponse['managers'][0]['data']);
    }

    private function insertVerse(int $id, ?array $data = null): void
    {
        Yii::$app->db->createCommand()->insert('{{%verse}}', [
            'id' => $id,
            'author_id' => 42,
            'name' => 'Verse ' . $id,
            'data' => json_encode($data ?? ['children' => ['modules' => []]]),
            'uuid' => 'verse-' . $id,
            'description' => 'Snapshot test verse',
        ])->execute();
    }

    private function insertFile(
        int $id,
        string $filename,
        string $key,
        string $type = 'application/octet-stream'
    ): void
    {
        Yii::$app->db->createCommand()->insert('{{%file}}', [
            'id' => $id,
            'md5' => 'md5-' . $id,
            'type' => $type,
            'url' => 'https://example.test/' . $filename,
            'filename' => $filename,
            'size' => 12345,
            'key' => $key,
        ])->execute();
    }

    private function insertSnapshotContractVerse(int $verseId): void
    {
        $this->insertVerse($verseId, [
            'type' => 'Verse',
            'children' => [
                'modules' => [
                    [
                        'type' => 'Module',
                        'parameters' => [
                            'meta_id' => 801,
                            'uuid' => 'module-801',
                        ],
                    ],
                ],
            ],
            'settings' => [
                'gravity' => 9.8,
            ],
        ]);
        Yii::$app->db->createCommand()->insert('{{%meta}}', [
            'id' => 801,
            'author_id' => 42,
            'uuid' => 'meta-801',
            'title' => 'Meta 801',
            'data' => '{"type":"MetaRoot","children":{"entities":[{"uuid":"entity-1"}]}}',
            'events' => '{"inputs":[{"key":"start"}],"outputs":[]}',
            'prefab' => 0,
            'code' => 'print("meta 801")',
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%verse_meta}}', [
            'verse_id' => $verseId,
            'meta_id' => 801,
        ])->execute();

        $this->insertFile(201, 'model.glb', 'model-key');
        $this->insertFile(202, 'model.jpg', 'model-image-key', 'image/jpeg');
        Yii::$app->db->createCommand()->insert('{{%resource}}', [
            'id' => 901,
            'name' => 'model.glb',
            'type' => 'polygen',
            'author_id' => 42,
            'file_id' => 201,
            'image_id' => 202,
            'info' => '{"size":{"x":1,"y":2,"z":3}}',
            'uuid' => 'resource-901',
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%meta_resource}}', [
            'meta_id' => 801,
            'resource_id' => 901,
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%manager}}', [
            'verse_id' => $verseId,
            'type' => 'score',
            'data' => '{"value":10}',
        ])->execute();

        $this->insertFile(301, 'space-mesh.glb', 'space-mesh-key', 'model/gltf-binary');
        $this->insertFile(302, 'space-thumbnail.png', 'space-thumbnail-key', 'image/png');
        $this->insertFile(303, 'space-runtime.bytes', 'space-runtime-key', 'application/octet-stream');
        Yii::$app->db->createCommand()->insert('{{%space}}', [
            'id' => 701,
            'name' => 'Contract Space',
            'user_id' => 42,
            'mesh_id' => 301,
            'image_id' => 302,
            'file_id' => 303,
            'data' => json_encode([
                'provider' => 'immersal',
            ]),
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%verse_space}}', [
            'verse_id' => $verseId,
            'space_id' => 701,
        ])->execute();
    }

    private function markSnapshotColumnsAsNativeJson(array $attributes): void
    {
        $tableSchema = Snapshot::getTableSchema();
        foreach ($attributes as $attribute) {
            $this->assertArrayHasKey($attribute, $tableSchema->columns);
            $tableSchema->columns[$attribute]->type = Schema::TYPE_JSON;
        }
    }
}
