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
