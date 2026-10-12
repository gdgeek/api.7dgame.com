<?php

namespace tests\unit\models;

use api\modules\v1\models\Tags;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

final class TagsPublicKeyTest extends TestCase
{
    private Connection $originalDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDb = Yii::$app->db;
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->createCommand()->createTable('tags', [
            'id' => 'pk', 'name' => 'string', 'key' => 'string', 'type' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('property', [
            'id' => 'pk', 'key' => 'string',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('verse_property', [
            'verse_id' => 'integer', 'property_id' => 'integer',
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDb);
        parent::tearDown();
    }

    public static function reservedKeys(): array
    {
        return [['public'], ['PUBLIC'], ['Public'], ['public '], [' public ']];
    }

    #[DataProvider('reservedKeys')]
    public function testCatalogCannotRecreatePublicTag(string $key): void
    {
        $tag = new Tags(['name' => 'Deprecated visibility tag', 'key' => $key, 'type' => 'Classify']);

        self::assertFalse($tag->save());
        self::assertTrue($tag->hasErrors('key'));
        self::assertSame(0, (int) Tags::find()->count());
    }

    public function testExistingClassificationCannotBeRenamedToPublic(): void
    {
        $tag = new Tags(['name' => 'Learning by doing', 'key' => 'learning-by-doing', 'type' => 'Classify']);
        self::assertTrue($tag->save());

        $tag->key = 'public';
        self::assertFalse($tag->save());
        self::assertTrue($tag->hasErrors('key'));
        self::assertSame('learning-by-doing', Tags::findOne($tag->id)->key);
    }

    public function testHistoricalPublicTagCanBeDeletedWithoutChangingPublicProperty(): void
    {
        Yii::$app->db->createCommand()->insert('tags', [
            'id' => 1, 'name' => 'Historical public', 'key' => 'public', 'type' => 'Status',
        ])->execute();
        Yii::$app->db->createCommand()->insert('property', ['id' => 1, 'key' => 'public'])->execute();
        Yii::$app->db->createCommand()->insert('verse_property', ['verse_id' => 7, 'property_id' => 1])->execute();
        $tag = Tags::findOne(1);

        $tag->name = 'Historical visibility label';
        self::assertTrue($tag->save(), 'An unchanged legacy key does not block cleanup metadata.');
        self::assertSame(1, $tag->delete());
        self::assertNull(Tags::findOne(1));
        self::assertSame('public', Yii::$app->db->createCommand('SELECT key FROM property WHERE id = 1')->queryScalar());
        self::assertSame(1, (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM verse_property WHERE verse_id = 7 AND property_id = 1')->queryScalar());
    }

    public function testHistoricalPublicKeyCanBeChangedToAnOrdinaryClassification(): void
    {
        Yii::$app->db->createCommand()->insert('tags', [
            'id' => 1, 'name' => 'Historical public', 'key' => 'public', 'type' => 'Status',
        ])->execute();
        $tag = Tags::findOne(1);
        $tag->key = 'ordinary-category';
        $tag->type = 'Classify';

        self::assertTrue($tag->save());
        self::assertSame('ordinary-category', Tags::findOne(1)->key);
    }
}
