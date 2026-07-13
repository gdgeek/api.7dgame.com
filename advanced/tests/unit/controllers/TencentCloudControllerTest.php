<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\TencentCloudController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\filters\AccessControl;
use yii\web\User;

final class TencentCloudControllerTest extends TestCase
{
    public function testAccessBehaviorUsesRuleAwareYiiFilter(): void
    {
        $controller = new TencentCloudController(
            'tencent-cloud',
            Yii::$app->getModule('v1')
        );

        $behaviors = $controller->behaviors();

        $this->assertSame(AccessControl::class, $behaviors['access']['class']);
        $this->assertSame(
            [['allow' => true, 'roles' => ['@']]],
            $behaviors['access']['rules']
        );
        $behaviorConfig = $behaviors['access'];
        $behaviorConfig['user'] = new User([
            'identityClass' => \common\models\User::class,
        ]);

        $this->assertInstanceOf(AccessControl::class, Yii::createObject($behaviorConfig));
    }
}
