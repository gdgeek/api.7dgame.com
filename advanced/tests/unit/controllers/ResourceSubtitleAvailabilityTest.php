<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\ResourceController;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Resource;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;

final class ResourceSubtitleAvailabilityTest extends TestCase
{
    private Connection $db;
    private Connection $originalDb;
    private array $originalEnv;

    protected function setUp(): void
    {
        $env = ['ENABLE_DEV_SUBTITLE_RESOURCES' => '1', 'FILE_STORAGE_DRIVER' => 'cos'];
        $this->originalEnv = [];
        foreach ($env as $key => $value) {
            $this->originalEnv[$key] = getenv($key);
            putenv($key . '=' . $value);
        }

        $this->originalDb = Yii::$app->get('db');
        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $this->db);
        $this->db->createCommand(
            'CREATE TABLE resource (id INTEGER PRIMARY KEY, type TEXT, author_id INTEGER, uuid TEXT, info TEXT,
            file_id INTEGER, image_id INTEGER, created_at TEXT, name TEXT)'
        )->execute();
        $this->db->createCommand(
            'CREATE TABLE resource_subtitle (media_resource_id INTEGER PRIMARY KEY, subtitle_resource_id INTEGER)'
        )->execute();
        $this->db->createCommand('CREATE TABLE meta (id INTEGER PRIMARY KEY)')->execute();
        $this->db->createCommand('CREATE TABLE meta_resource (meta_id INTEGER, resource_id INTEGER)')->execute();
        $this->db->createCommand()->batchInsert('resource', ['id', 'type', 'author_id', 'uuid', 'info'], [
            [1, 'audio', 7, 'audio', '{"length":10}'],
            [2, 'video', 7, 'video', '{"length":10}'],
            [3, 'video', 7, 'video-bad-owner', '{"subtitleResourceId":12}'],
            [4, 'audio', 7, 'audio-bad-type', '{}'],
            [5, 'audio', 7, 'audio-unbound', '{}'],
            [10, 'subtitle', 7, 'subtitle', '{}'],
            [11, 'picture', 7, 'picture', '{}'],
            [12, 'subtitle', 8, 'subtitle-other-owner', '{}'],
        ])->execute();
        $this->db->createCommand()->batchInsert('resource_subtitle', ['media_resource_id', 'subtitle_resource_id'], [
            [1, 10], [2, 10], [3, 12], [4, 11],
        ])->execute();
        $this->db->createCommand()->batchInsert('meta_resource', ['meta_id', 'resource_id'], [
            [7, 1], [7, 2], [7, 3], [7, 4], [7, 5], [7, 11],
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->set('db', $this->originalDb);
        $this->db->close();
        foreach ($this->originalEnv as $key => $value) {
            putenv($value === false ? $key : $key . '=' . $value);
        }
    }

    public function testMediaDetailExposesOnlyTheBindingFlag(): void
    {
        $controller = new ResourceController('resource', Yii::$app);

        self::assertSame(['info' => '{"length":10}', 'hasSubtitle' => true],
            $controller->actionView(1)->toArray(['info', 'hasSubtitle']));
        self::assertSame(['info' => '{"subtitleResourceId":12}', 'hasSubtitle' => false],
            $controller->actionView(3)->toArray(['info', 'hasSubtitle']));
        self::assertSame(['info' => '{}'], $controller->actionView(11)->toArray(['info', 'hasSubtitle']));
    }

    public function testEntityResourcesOnlyReportValidMediaBindings(): void
    {
        $meta = new Meta(['id' => 7]);

        $resources = array_map(
            static fn (Resource $resource) => $resource->toArray(['id', 'hasSubtitle']),
            $meta->getResources()
        );

        self::assertSame([
            1 => ['id' => 1, 'hasSubtitle' => true],
            2 => ['id' => 2, 'hasSubtitle' => true],
            3 => ['id' => 3, 'hasSubtitle' => false],
            4 => ['id' => 4, 'hasSubtitle' => false],
            5 => ['id' => 5, 'hasSubtitle' => false],
            11 => ['id' => 11],
        ], array_column($resources, null, 'id'));
    }

    public function testResourceListEnrichesTheExistingAuthorizedSelection(): void
    {
        $user = Yii::$app->get('user', false);
        Yii::$app->set('user', new class extends Component {
            public int $id = 7;
        });
        try {
            $models = (new ResourceController('resource', Yii::$app))->actionIndex()->getModels();
            $resources = array_map(static fn (Resource $resource) => $resource->toArray(['id', 'hasSubtitle']), $models);
            $byId = array_column($resources, null, 'id');
            ksort($byId);

            self::assertSame([1, 2, 3, 4, 5, 11], array_keys($byId));
            self::assertTrue($byId[1]['hasSubtitle']);
            self::assertFalse($byId[5]['hasSubtitle']);
            self::assertArrayNotHasKey('hasSubtitle', $byId[11]);
        } finally {
            Yii::$app->set('user', $user);
        }
    }

    public function testFeatureDisabledDoesNotRequireTheBindingTable(): void
    {
        putenv('ENABLE_DEV_SUBTITLE_RESOURCES=0');
        $this->db->createCommand()->dropTable('resource_subtitle')->execute();

        $controller = new ResourceController('resource', Yii::$app);

        self::assertSame(['info' => '{"length":10}', 'hasSubtitle' => false],
            $controller->actionView(1)->toArray(['info', 'hasSubtitle']));
    }
}
