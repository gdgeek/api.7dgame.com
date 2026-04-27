<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginArSlamLocalizationController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

final class PluginArSlamLocalizationControllerTest extends TestCase
{
    private const VERSE_A = 501;
    private const VERSE_B = 502;
    private const VERSE_C = 503;
    private const SPACE_ALPHA = 701;
    private const SPACE_BETA = 702;
    private const SPACE_FOREIGN = 703;

    private $originalRequestComponent;
    private $originalResponseComponent;
    private $originalUserComponent;
    private $originalDbComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRequestComponent = Yii::$app->get('request', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->originalUserComponent = Yii::$app->get('user', false);
        $this->originalDbComponent = Yii::$app->get('db', false);
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%user}}', [
            'id' => 'pk',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%file}}', [
            'id' => 'pk',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%verse}}', [
            'id' => 'pk',
            'name' => 'string',
            'author_id' => 'integer',
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
        Yii::$app->db->createCommand()->batchInsert('{{%user}}', ['id'], [[42], [99]])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%file}}', ['id'], [[601], [602], [603], [604]])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%verse}}', ['id', 'name', 'author_id'], [
            [self::VERSE_A, 'Verse A', 42],
            [self::VERSE_B, 'Verse B', 42],
            [self::VERSE_C, 'Verse C', 42],
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert(
            '{{%space}}',
            ['id', 'name', 'user_id', 'mesh_id', 'image_id', 'file_id', 'created_at', 'data'],
            [
                [self::SPACE_ALPHA, 'SLAM Alpha', 42, 601, 602, null, '2026-04-27 00:00:00', '{"provider":"immersal"}'],
                [self::SPACE_BETA, 'SLAM Beta', 42, 603, null, null, '2026-04-27 00:00:00', '{"provider":"area-target-scanner"}'],
                [self::SPACE_FOREIGN, 'SLAM Foreign', 99, 604, null, null, '2026-04-27 00:00:00', '{"provider":"foreign"}'],
            ]
        )->execute();
        Yii::$app->set('user', new ArSlamLocalizationTestUser());
        Yii::$app->set('response', new Response());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('request', $this->originalRequestComponent);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testCreateBindingsAllowsOneSpaceToBindMultipleVerses(): void
    {
        Yii::$app->set('request', new ArSlamLocalizationTestRequest([], [
            'spaceId' => self::SPACE_ALPHA,
            'verseIds' => [self::VERSE_A, self::VERSE_B],
        ]));

        $controller = new PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );
        $result = $controller->actionCreateBindings();

        $this->assertSame(0, $result['code']);
        $this->assertSame(['spaceId' => self::SPACE_ALPHA, 'verseIds' => [self::VERSE_A, self::VERSE_B]], $result['data']);

        Yii::$app->set('request', new ArSlamLocalizationTestRequest([
            'verseIds' => self::VERSE_A . ',' . self::VERSE_B,
        ], []));

        $bindings = $controller->actionBindings();
        usort($bindings, static fn (array $left, array $right): int => $left['verseId'] <=> $right['verseId']);

        $this->assertSame([
            ['verseId' => self::VERSE_A, 'spaceId' => self::SPACE_ALPHA, 'spaceName' => 'SLAM Alpha'],
            ['verseId' => self::VERSE_B, 'spaceId' => self::SPACE_ALPHA, 'spaceName' => 'SLAM Alpha'],
        ], $bindings);
    }

    public function testCreateBindingsRejectsBindingOneVerseToAnotherSpace(): void
    {
        $controller = new PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );

        Yii::$app->set('request', new ArSlamLocalizationTestRequest([], [
            'spaceId' => self::SPACE_ALPHA,
            'verseIds' => [self::VERSE_C],
        ]));
        $controller->actionCreateBindings();

        Yii::$app->set('request', new ArSlamLocalizationTestRequest([], [
            'spaceId' => self::SPACE_BETA,
            'verseIds' => [self::VERSE_C],
        ]));

        $this->expectException(ConflictHttpException::class);
        $controller->actionCreateBindings();
    }

    public function testCreateBindingsRejectsAnotherUsersSpace(): void
    {
        Yii::$app->set('request', new ArSlamLocalizationTestRequest([], [
            'spaceId' => self::SPACE_FOREIGN,
            'verseIds' => [self::VERSE_A],
        ]));

        $controller = new PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionCreateBindings();
    }

    public function testDeleteBindingUnbindsVerseFromCurrentUsersSpace(): void
    {
        Yii::$app->db->createCommand()->insert('{{%verse_space}}', [
            'verse_id' => self::VERSE_A,
            'space_id' => self::SPACE_ALPHA,
            'created_at' => '2026-04-27 00:00:00',
        ])->execute();

        $controller = new PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );
        $result = $controller->actionDeleteBinding(self::VERSE_A);

        $this->assertSame(0, $result['code']);
        $this->assertSame(['verseId' => self::VERSE_A], $result['data']);
        $this->assertSame(
            0,
            (int) Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%verse_space}} WHERE verse_id = :verse_id', [
                ':verse_id' => self::VERSE_A,
            ])->queryScalar()
        );
    }

    public function testDeleteBindingRejectsAnotherUsersSpace(): void
    {
        Yii::$app->db->createCommand()->insert('{{%verse_space}}', [
            'verse_id' => self::VERSE_A,
            'space_id' => self::SPACE_FOREIGN,
            'created_at' => '2026-04-27 00:00:00',
        ])->execute();

        $controller = new PluginArSlamLocalizationController(
            'plugin-ar-slam-localization',
            Yii::$app->getModule('v1')
        );

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionDeleteBinding(self::VERSE_A);
    }

}

final class ArSlamLocalizationTestRequest extends \yii\web\Request
{
    public function __construct(
        private array $queryParams,
        private array $bodyParams,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$name] ?? $defaultValue;
    }

    public function getBodyParam($name, $defaultValue = null)
    {
        return $this->bodyParams[$name] ?? $defaultValue;
    }

    public function getBodyParams()
    {
        return $this->bodyParams;
    }
}

final class ArSlamLocalizationTestUser extends Component
{
    public int $id = 42;
    public object $identity;

    public function init(): void
    {
        parent::init();
        $this->identity = (object) ['id' => $this->id];
    }
}
