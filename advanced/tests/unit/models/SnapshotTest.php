<?php

namespace tests\unit\models;

use api\modules\v1\models\Snapshot;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

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
        $this->insertVerse(503, [
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
        ]);
        Yii::$app->db->createCommand()->insert('{{%meta}}', [
            'id' => 801,
            'author_id' => 42,
            'uuid' => 'meta-801',
            'title' => 'Meta 801',
            'data' => '{"type":"MetaRoot","children":{"entities":[]}}',
            'events' => '{"inputs":[],"outputs":[]}',
            'prefab' => 0,
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%verse_meta}}', [
            'verse_id' => 503,
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
            'verse_id' => 503,
            'type' => 'score',
            'data' => '{"value":10}',
        ])->execute();

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
}
