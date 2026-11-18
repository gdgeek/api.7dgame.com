<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\SubtitleController;
use api\modules\v1\models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;
use yii\web\ForbiddenHttpException;

final class SubtitleAccessTest extends TestCase
{
    public static function sessions(): iterable
    {
        yield 'feature disabled' => ['0', 'local', [], 10, ['admin'], false];
        yield 'unknown storage' => ['1', 'invalid', [], 10, ['admin'], false];
        yield 'device session' => ['1', 'local', ['auth_method' => 'device_sn', 'device_sn_id' => 2], 10, ['admin'], false];
        yield 'account disabled after login' => ['1', 'local', [], 0, ['admin'], false];
        yield 'manager' => ['1', 'local', [], 10, ['manager'], false];
        yield 'ordinary account' => ['1', 'local', [], 10, ['user'], false];
        yield 'no roles' => ['1', 'local', [], 10, [], false];
        yield 'admin' => ['1', 'local', [], 10, ['admin'], true];
        yield 'root' => ['1', 'local', [], 10, ['root'], true];
    }

    #[DataProvider('sessions')]
    public function testSubtitleManagementRequiresAnEligibleAdminOrRootSession(
        string $flag,
        string $storage,
        array $context,
        int $status,
        array $roles,
        bool $allowed
    ): void {
        $env = ['ENABLE_DEV_SUBTITLE_RESOURCES' => $flag, 'FILE_STORAGE_DRIVER' => $storage];
        $previousEnv = [];
        $components = [];
        foreach ($env as $key => $value) {
            $previousEnv[$key] = getenv($key);
            putenv($key . '=' . $value);
        }
        foreach (['db', 'subtitleDb', 'user', 'response'] as $key) {
            $components[$key] = Yii::$app->get($key, false);
        }
        $db = new Connection(['dsn' => 'sqlite::memory:']);
        try {
            Yii::$app->set('db', $db);
            Yii::$app->set('subtitleDb', $db);
            Yii::$app->set('user', new \yii\web\User(['identityClass' => User::class, 'enableSession' => false]));
            Yii::$app->set('response', new \yii\web\Response());
            $db->createCommand('CREATE TABLE user (id INTEGER PRIMARY KEY, status INTEGER)')->execute();
            $db->createCommand('CREATE TABLE resource (id INTEGER PRIMARY KEY, type TEXT, author_id INTEGER)')->execute();
            $db->createCommand()->insert('user', ['id' => 1, 'status' => 10])->execute();
            $user = SubtitleTestIdentity::findOne(1);
            $user->authContext = $context;
            $user->assignedRoles = $roles;
            Yii::$app->user->setIdentity($user);
            $db->createCommand()->update('user', ['status' => $status], ['id' => 1])->execute();
            $controller = new SubtitleController('subtitle', Yii::$app);
            if (!$allowed) {
                $this->expectException(ForbiddenHttpException::class);
                $controller->actionIndex();
                return;
            }
            self::assertSame(['items' => [], 'total' => 0], $controller->actionIndex());
        } finally {
            foreach ($components as $key => $component) Yii::$app->set($key, $component);
            foreach ($previousEnv as $key => $value) putenv($value === false ? $key : $key . '=' . $value);
            $db->close();
        }
    }
}

final class SubtitleTestIdentity extends User
{
    public array $assignedRoles = [];

    public function getRoles()
    {
        return $this->assignedRoles;
    }
}
