<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\SystemController;
use api\modules\v1\controllers\TagsController;
use api\modules\v1\controllers\VerseController;
use api\modules\v1\controllers\VerseTagsController;
use api\modules\v1\models\Property;
use api\modules\v1\models\TagsSearch;
use api\modules\v1\models\Verse;
use api\modules\v1\models\VerseProperty;
use api\modules\v1\models\VerseTags;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\BadRequestHttpException;
use yii\web\Request;

final class ScenePublicPropertyTest extends TestCase
{
    private array $originalComponents = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['db', 'request', 'user'] as $id) {
            $this->originalComponents[$id] = Yii::$app->get($id, false);
        }
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->set('request', new Request());
        Yii::$app->set('user', new ScenePublicPropertyTestUser());
        $db = Yii::$app->db;
        $db->open();
        foreach ([
            'verse' => ['id' => 'pk', 'author_id' => 'integer', 'updater_id' => 'integer', 'image_id' => 'integer',
                'name' => 'string', 'info' => 'text', 'data' => 'text', 'uuid' => 'string',
                'created_at' => 'datetime', 'updated_at' => 'datetime'],
            'tags' => ['id' => 'pk', 'name' => 'string', 'key' => 'string', 'type' => 'string'],
            'verse_tags' => ['id' => 'pk', 'verse_id' => 'integer', 'tags_id' => 'integer'],
            'property' => ['id' => 'pk', 'key' => 'string', 'info' => 'text'],
            'verse_property' => ['id' => 'pk', 'verse_id' => 'integer', 'property_id' => 'integer'],
            'version' => ['id' => 'pk', 'name' => 'string', 'number' => 'integer', 'created_at' => 'datetime', 'info' => 'text'],
            'meta' => ['id' => 'pk'],
            'verse_meta' => ['id' => 'pk', 'verse_id' => 'integer', 'meta_id' => 'integer'],
            'verse_code' => ['id' => 'pk', 'verse_id' => 'integer', 'code_id' => 'integer'],
        ] as $table => $columns) {
            $db->createCommand()->createTable($table, $columns)->execute();
        }
        $db->createCommand()->batchInsert('verse', ['id', 'author_id', 'name', 'uuid'], [
            [1, 42, 'Old tag only', 'scene-1'],
            [2, 42, 'Property only', 'scene-2'],
            [3, 42, 'Property and old tag', 'scene-3'],
            [4, 42, 'Private category', 'scene-4'],
            [5, 7, 'Other author public scene', 'scene-5'],
        ])->execute();
        $db->createCommand()->batchInsert('tags', ['id', 'name', 'key', 'type'], [
            [1, 'Old public', 'public', 'Status'],
            [2, 'Category A', 'category-a', 'Classify'],
            [3, 'Category B', 'category-b', 'Classify'],
            [4, 'Checkin', 'checkin', 'Status'],
        ])->execute();
        $db->createCommand()->batchInsert('verse_tags', ['verse_id', 'tags_id'], [
            [1, 1], [1, 4], [2, 2], [3, 1], [3, 3], [4, 2], [5, 2],
        ])->execute();
        $db->createCommand()->insert('property', ['id' => 1, 'key' => 'public'])->execute();
        $db->createCommand()->batchInsert('verse_property', ['verse_id', 'property_id'], [[2, 1], [3, 1], [5, 1]])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        foreach ($this->originalComponents as $id => $component) {
            Yii::$app->set($id, $component);
        }
        parent::tearDown();
    }

    public function testPublicListDependsOnlyOnPropertyEvenAfterOldTagIsDeleted(): void
    {
        $this->assertSame([2, 3, 5], $this->sceneIds([], true));
        Yii::$app->db->createCommand()->delete('verse_tags', ['tags_id' => 1])->execute();
        Yii::$app->db->createCommand()->delete('tags', ['id' => 1])->execute();
        $this->assertSame([2, 3, 5], $this->sceneIds([], true));
    }

    public function testPublicSceneWithNoTagsRemainsVisibleInPropertyQueries(): void
    {
        Yii::$app->db->createCommand()->delete('verse_tags', ['verse_id' => 2])->execute();

        $this->assertSame([2, 3, 5], $this->sceneIds([], true));
        $this->assertSame([2, 3], $this->sceneIds(['public' => '1']));
        $this->assertSame([], Verse::findOne(2)->getTags()->column());
    }

    #[DataProvider('publicFilterValues')]
    public function testPublicFilterUsesPropertyWithinCurrentAuthorsScenes($value, array $expected): void
    {
        $this->assertSame($expected, $this->sceneIds(['public' => $value]));
    }

    public static function publicFilterValues(): array
    {
        return [
            ['1', [2, 3]], ['true', [2, 3]], [true, [2, 3]], [1, [2, 3]],
            ['0', [1, 4]], ['false', [1, 4]], [false, [1, 4]], [0, [1, 4]],
        ];
    }

    #[DataProvider('invalidPublicFilterValues')]
    public function testInvalidPublicFilterFailsInsteadOfSilentlyBroadeningResults($value): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->sceneIds(['public' => $value]);
    }

    public static function invalidPublicFilterValues(): array
    {
        return [[''], ['yes'], ['2'], ['FALSE'], [' true '], [null], [['1']], [1.0]];
    }

    public function testPrivateFilterCannotExposePrivateScenesThroughPublicList(): void
    {
        $this->assertSame([], $this->sceneIds(['public' => '0'], true));
        $this->assertSame([2, 3, 5], $this->sceneIds(['public' => '1'], true));
    }

    public function testClassificationFiltersIgnoreOldPublicIdAndPreserveOrSemantics(): void
    {
        $this->assertSame([], $this->sceneIds(['tags' => '1']));
        $this->assertSame([], $this->sceneIds(['tags' => '1'], true));
        $this->assertSame([2, 4], $this->sceneIds(['tags' => '1,2']));
        $this->assertSame([2, 3, 4], $this->sceneIds(['tags' => '2,3']));
        $this->assertSame([2], $this->sceneIds(['tags' => '2', 'public' => '1']));
        $this->assertSame([4], $this->sceneIds(['tags' => '2', 'public' => '0']));
        $this->assertSame([2, 5], $this->sceneIds(['tags' => '1,2'], true));
    }

    public function testCatalogAndExpandedSceneTagsHideOldPublicTag(): void
    {
        $catalog = new TagsController('tags', Yii::$app);
        $this->assertSame([2, 3, 4], array_map('intval', $catalog->actionIndex()->query->orderBy('id')->select('id')->column()));
        $this->assertSame([], (new TagsSearch())->search(['TagsSearch' => ['key' => 'public']])->query->column());
        $this->assertSame([3], array_map('intval', Verse::findOne(3)->getTags()->select('tags.id')->column()));
        $this->assertSame([4], array_map('intval', Verse::findOne(1)->getTags()->select('tags.id')->column()));
    }

    #[DataProvider('tagAddEndpoints')]
    public function testBothTagAddEndpointsRejectPublicWithoutChangingVisibility(string $endpoint): void
    {
        $beforeLinks = (int) VerseTags::find()->count();
        $beforeProperties = (int) VerseProperty::find()->count();
        try {
            if ($endpoint === 'verse') {
                $this->verseController()->actionAddTag(4, 1);
            } else {
                Yii::$app->request->setBodyParams(['verse_id' => 4, 'tags_id' => 1]);
                (new VerseTagsController('verse-tags', Yii::$app))->actionCreate();
            }
            $this->fail('Old public tag must be rejected');
        } catch (BadRequestHttpException $e) {
            $this->assertSame(400, $e->statusCode);
            $this->assertSame($beforeLinks, (int) VerseTags::find()->count());
            $this->assertSame($beforeProperties, (int) VerseProperty::find()->count());
            $this->assertFalse(Verse::findOne(4)->public);
        }
    }

    public static function tagAddEndpoints(): array
    {
        return [['verse'], ['verse-tags']];
    }

    public function testClassificationAndPublicWritesRemainIndependent(): void
    {
        $controller = $this->verseController();
        $controller->actionAddTag(1, 2);
        $this->assertFalse(Verse::findOne(1)->public);
        $controller->actionAddPublic(1);
        $this->assertTrue(Verse::findOne(1)->public);
        $controller->actionRemoveTag(1, 1);
        $this->assertTrue(Verse::findOne(1)->public);
        $controller->actionRemovePublic(1);
        $this->assertFalse(Verse::findOne(1)->public);
        $this->assertTrue(VerseTags::find()->where(['verse_id' => 1, 'tags_id' => 2])->exists());
    }

    public function testUpgradeDoesNotRepublishOldTaggedScenesAndStillMigratesCheckin(): void
    {
        $result = (new SystemController('system', Yii::$app))->actionUpgrade();

        $this->assertSame('skipped', $result['tags']['public']['status']);
        $this->assertFalse(Verse::findOne(1)->public);
        $this->assertSame([2, 3, 5], $this->sceneIds([], true));
        $this->assertSame('success', $result['tags']['checkin']['status']);
        $checkin = Property::findOne(['key' => 'checkin']);
        $this->assertNotNull($checkin);
        $this->assertTrue(VerseProperty::find()->where(['verse_id' => 1, 'property_id' => $checkin->id])->exists());
        $this->assertSame(0, $result['verse']['fail']);
    }

    private function verseController(): VerseController
    {
        return new VerseController('verse', Yii::$app);
    }

    private function sceneIds(array $params, bool $publicList = false): array
    {
        Yii::$app->request->setQueryParams($params);
        $controller = $this->verseController();
        $provider = $publicList ? $controller->actionPublic() : $controller->actionIndex();
        return array_map('intval', $provider->query->orderBy('verse.id')->select('verse.id')->column());
    }
}

final class ScenePublicPropertyTestUser extends Component
{
    public int $id = 42;
    public object $identity;

    public function init(): void
    {
        parent::init();
        $this->identity = (object) ['id' => $this->id];
    }

    public function can($permissionName, $params = [], $allowCaching = true): bool
    {
        return true;
    }
}
