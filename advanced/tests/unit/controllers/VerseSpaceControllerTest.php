<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\VerseSpaceController;
use api\modules\v1\models\VerseSpace;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;

final class VerseSpaceControllerTest extends TestCase
{
    private $originalDbComponent;
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDbComponent = Yii::$app->get('db', false);
        $this->originalUserComponent = Yii::$app->get('user', false);

        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%verse}}', [
            'id' => 'pk',
            'author_id' => 'integer',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%space}}', [
            'id' => 'pk',
            'name' => 'string not null',
            'user_id' => 'integer not null',
            'mesh_id' => 'integer not null',
            'file_id' => 'integer',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%verse_space}}', [
            'id' => 'pk',
            'verse_id' => 'integer not null unique',
            'space_id' => 'integer not null',
            'created_at' => 'datetime',
        ])->execute();

        Yii::$app->db->createCommand()->batchInsert('{{%verse}}', ['id', 'author_id'], [
            [501, 42],
            [502, 7],
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%space}}', ['id', 'name', 'user_id', 'mesh_id'], [
            [701, 'Mine', 42, 601],
            [702, 'Other', 7, 602],
        ])->execute();
        Yii::$app->db->createCommand()->batchInsert('{{%verse_space}}', ['id', 'verse_id', 'space_id', 'created_at'], [
            [801, 501, 701, '2026-04-27 00:00:00'],
            [802, 502, 702, '2026-04-27 00:00:00'],
        ])->execute();

        Yii::$app->set('user', new VerseSpaceControllerTestUser());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        parent::tearDown();
    }

    public function testIndexOnlyReturnsBindingsForCurrentUsersSpaces(): void
    {
        $controller = new VerseSpaceController('verse-space', Yii::$app->getModule('v1'));

        $models = $controller->actionIndex()->getModels();

        $this->assertCount(1, $models);
        $this->assertSame(801, (int) $models[0]->id);
    }

    public function testCheckAccessRejectsAnotherUsersSpaceBinding(): void
    {
        $controller = new VerseSpaceController('verse-space', Yii::$app->getModule('v1'));
        $binding = VerseSpace::findOne(802);

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('view', $binding);
    }
}

final class VerseSpaceControllerTestUser extends Component
{
    public int $id = 42;
    public object $identity;

    public function init(): void
    {
        parent::init();
        $this->identity = (object) ['id' => $this->id];
    }
}
