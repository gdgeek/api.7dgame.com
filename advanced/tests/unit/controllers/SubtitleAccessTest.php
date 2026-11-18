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
    public static function deniedSessions(): iterable
    {
        yield 'feature disabled' => ['0', 'local', [], 10];
        yield 'unknown storage' => ['1', 'invalid', [], 10];
        yield 'device session' => ['1', 'local', ['auth_method' => 'device_sn', 'device_sn_id' => 2], 10];
        yield 'account disabled after login' => ['1', 'local', [], 0];
    }

    #[DataProvider('deniedSessions')]
    public function testSubtitleApiRejectsIneligibleSessions(string $flag, string $storage, array $context, int $status): void
    {
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
            $db->createCommand()->insert('user', ['id' => 1, 'status' => 10])->execute();
            $user = User::findOne(1);
            $user->authContext = $context;
            Yii::$app->user->setIdentity($user);
            $db->createCommand()->update('user', ['status' => $status], ['id' => 1])->execute();
            $this->expectException(ForbiddenHttpException::class);
            (new SubtitleController('subtitle', Yii::$app))->actionIndex();
        } finally {
            foreach ($components as $key => $component) Yii::$app->set($key, $component);
            foreach ($previousEnv as $key => $value) putenv($value === false ? $key : $key . '=' . $value);
            $db->close();
        }
    }
}
