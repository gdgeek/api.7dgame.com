<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\GroupController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

final class GroupVerseRemovalActionTest extends TestCase
{
    private $originalCacheComponent;
    private $originalDbComponent;
    private $originalResponseComponent;
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalCacheComponent = Yii::$app->get('cache', false);
        $this->originalDbComponent = Yii::$app->get('db', false);
        $this->originalResponseComponent = Yii::$app->get('response', false);
        $this->originalUserComponent = Yii::$app->get('user', false);

        Yii::$app->set('cache', new ArrayCache());
        Yii::$app->set('db', new Connection(['dsn' => 'sqlite::memory:']));
        Yii::$app->db->open();
        Yii::$app->db->createCommand()->createTable('{{%group}}', [
            'id' => 'pk',
            'user_id' => 'integer not null',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%verse}}', [
            'id' => 'pk',
            'author_id' => 'integer not null',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%group_verse}}', [
            'id' => 'pk',
            'group_id' => 'integer not null',
            'verse_id' => 'integer not null',
        ])->execute();
        Yii::$app->set('response', new Response());
        Yii::$app->set('user', new GroupVerseRemovalTestUser());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        Yii::$app->set('response', $this->originalResponseComponent);
        Yii::$app->db->close();
        Yii::$app->set('db', $this->originalDbComponent);
        Yii::$app->set('cache', $this->originalCacheComponent);
        parent::tearDown();
    }

    public function testOrdinaryMemberCannotRemoveAnotherAuthorsScene(): void
    {
        $this->seed(1, 7, 10, 8);

        try {
            $this->controller()->actionDeleteVerse(1, 10);
            $this->fail('Expected removal to be forbidden');
        } catch (ForbiddenHttpException) {
            $this->assertSame(1, $this->rowCount('group_verse'));
            $this->assertSame(1, $this->rowCount('verse'));
        }
    }

    public function testGroupOwnerRemovesLinkButKeepsAnotherAuthorsScene(): void
    {
        $this->seed(1, 42, 10, 8);

        $this->controller()->actionDeleteVerse(1, 10);

        $this->assertSame(0, $this->rowCount('group_verse'));
        $this->assertSame(1, $this->rowCount('verse'));
        $this->assertSame(204, Yii::$app->response->statusCode);
    }

    public function testSceneAuthorRemovesAndDeletesOwnOrphanedScene(): void
    {
        $this->seed(1, 7, 10, 42);

        $this->controller()->actionDeleteVerse(1, 10);

        $this->assertSame(0, $this->rowCount('group_verse'));
        $this->assertSame(0, $this->rowCount('verse'));
    }

    private function controller(): GroupController
    {
        return new GroupController('group', Yii::$app->getModule('v1'));
    }

    private function seed(int $groupId, int $groupOwnerId, int $verseId, int $verseAuthorId): void
    {
        Yii::$app->db->createCommand()->insert('{{%group}}', [
            'id' => $groupId,
            'user_id' => $groupOwnerId,
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%verse}}', [
            'id' => $verseId,
            'author_id' => $verseAuthorId,
        ])->execute();
        Yii::$app->db->createCommand()->insert('{{%group_verse}}', [
            'group_id' => $groupId,
            'verse_id' => $verseId,
        ])->execute();
    }

    private function rowCount(string $table): int
    {
        $quotedTable = Yii::$app->db->quoteTableName($table);
        return (int) Yii::$app->db->createCommand("SELECT COUNT(*) FROM {$quotedTable}")
            ->queryScalar();
    }
}

final class GroupVerseRemovalTestUser extends Component
{
    public int $id = 42;
}
