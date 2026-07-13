<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\GroupController;
use api\modules\v1\models\Group;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

final class GroupControllerAccessTest extends TestCase
{
    private $originalUserComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalUserComponent = Yii::$app->get('user', false);
        Yii::$app->set('user', new GroupAccessTestUser());
    }

    protected function tearDown(): void
    {
        Yii::$app->set('user', $this->originalUserComponent);
        parent::tearDown();
    }

    public function testOnlyOwnerMayUpdateOrDeleteGroup(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));
        $controller->checkAccess('update', new AccessGroup(['user_id' => 42]));

        $this->expectException(ForbiddenHttpException::class);
        $controller->checkAccess('delete', new AccessGroup(['user_id' => 7]));
    }

    public function testMemberMayChangeGroupScenes(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));
        $controller->requireMember(new AccessGroup(['joinedValue' => true]));

        $this->assertTrue(true);
    }

    public function testOutsiderMayNotChangeGroupScenes(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));

        $this->expectException(ForbiddenHttpException::class);
        $controller->requireMember(new AccessGroup(['joinedValue' => false]));
    }

    public function testGroupOwnerMayRemoveAnotherAuthorsSceneWithoutDeletingIt(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));
        $verse = new AccessVerse(['author_id' => 7]);

        $controller->requireRemovalAllowed(new AccessGroup(['user_id' => 42]), $verse);

        $this->assertFalse($controller->mayDeleteOrphan($verse));
    }

    public function testSceneAuthorMayRemoveAndDeleteOwnOrphanedScene(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));
        $verse = new AccessVerse(['author_id' => 42]);

        $controller->requireRemovalAllowed(new AccessGroup(['user_id' => 7]), $verse);

        $this->assertTrue($controller->mayDeleteOrphan($verse));
    }

    public function testOrdinaryMemberMayNotRemoveAnotherAuthorsScene(): void
    {
        $controller = new TestableGroupController('group', Yii::$app->getModule('v1'));

        $this->expectException(ForbiddenHttpException::class);
        $controller->requireRemovalAllowed(
            new AccessGroup(['user_id' => 7, 'joinedValue' => true]),
            new AccessVerse(['author_id' => 8])
        );
    }
}

final class GroupAccessTestUser extends Component
{
    public int $id = 42;
}

final class AccessGroup extends Group
{
    public bool $joinedValue = false;

    public function attributes(): array
    {
        return ['user_id'];
    }

    public function getJoined(): bool
    {
        return $this->joinedValue;
    }
}

final class AccessVerse extends \api\modules\v1\models\Verse
{
    public function attributes(): array
    {
        return ['author_id'];
    }
}

final class TestableGroupController extends GroupController
{
    public function requireMember(Group $group): void
    {
        $this->ensureGroupMember($group);
    }

    public function requireRemovalAllowed(Group $group, \api\modules\v1\models\Verse $verse): void
    {
        $this->ensureVerseRemovalAllowed($group, $verse);
    }

    public function mayDeleteOrphan(\api\modules\v1\models\Verse $verse): bool
    {
        return $this->shouldDeleteOrphanedVerse($verse);
    }
}
