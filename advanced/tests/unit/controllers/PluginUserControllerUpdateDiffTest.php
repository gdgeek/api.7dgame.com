<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\PluginUserController;
use api\modules\v1\models\User;
use PHPUnit\Framework\TestCase;
use Yii;

final class PluginUserControllerUpdateDiffTest extends TestCase
{
    public function testCollectManagedUserDirtyAttributesSkipsUnchangedScalarFields(): void
    {
        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $user = new User();
        $user->nickname = 'Alice';
        $user->email = 'alice@example.com';
        $user->status = 10;

        $method = new \ReflectionMethod($controller, 'collectManagedUserDirtyAttributes');

        [$requestedUpdate, $dirtyAttributes] = $method->invoke($controller, $user, [
            'nickname' => 'Alice',
            'email' => 'alice@example.com',
            'status' => 10,
        ]);

        $this->assertTrue($requestedUpdate);
        $this->assertSame([], $dirtyAttributes);
        $this->assertSame('Alice', $user->nickname);
        $this->assertSame('alice@example.com', $user->email);
        $this->assertSame(10, $user->status);
    }

    public function testCollectManagedUserDirtyAttributesTracksChangedFieldsAndNormalizesNickname(): void
    {
        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $user = new User();
        $user->nickname = 'Alice';
        $user->email = 'alice@example.com';
        $user->status = 10;
        $user->password_hash = 'old-hash';

        $method = new \ReflectionMethod($controller, 'collectManagedUserDirtyAttributes');

        [$requestedUpdate, $dirtyAttributes] = $method->invoke($controller, $user, [
            'nickname' => '   ',
            'email' => 'alice+updated@example.com',
            'status' => '0',
            'password' => 'Secret123!',
        ]);

        $this->assertTrue($requestedUpdate);
        $this->assertSame(['nickname', 'email', 'status', 'password_hash'], $dirtyAttributes);
        $this->assertNull($user->nickname);
        $this->assertSame('alice+updated@example.com', $user->email);
        $this->assertSame(0, $user->status);
        $this->assertNotSame('old-hash', $user->password_hash);
    }

    public function testCollectManagedUserDirtyAttributesNormalizesBlankEmailToNull(): void
    {
        $controller = new PluginUserController('plugin-user', Yii::$app->getModule('v1'));
        $user = new User();
        $user->email = 'alice@example.com';

        $method = new \ReflectionMethod($controller, 'collectManagedUserDirtyAttributes');

        [$requestedUpdate, $dirtyAttributes] = $method->invoke($controller, $user, [
            'email' => '   ',
        ]);

        $this->assertTrue($requestedUpdate);
        $this->assertSame(['email'], $dirtyAttributes);
        $this->assertNull($user->email);
    }
}
