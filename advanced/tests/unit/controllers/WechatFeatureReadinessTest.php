<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\WechatController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Response;

final class WechatFeatureReadinessTest extends TestCase
{
    private array $originalEnv = [];
    private $originalResponseComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalResponseComponent = Yii::$app->get('response', false);
        Yii::$app->set('response', new Response());
        foreach (['DEPLOYMENT_MODE', 'ENABLE_WECHAT_LOGIN', 'WECHAT_APP_ID', 'WECHAT_APPID', 'WECHAT_SECRET', 'WECHAT_TOKEN'] as $name) {
            $value = getenv($name);
            $this->originalEnv[$name] = $value === false ? null : $value;
            putenv($name);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $name => $value) {
            $value === null ? putenv($name) : putenv($name . '=' . $value);
        }
        Yii::$app->set('response', $this->originalResponseComponent);
        parent::tearDown();
    }

    public function testQrcodeIsDisabledWhenCredentialsAreMissing(): void
    {
        putenv('DEPLOYMENT_MODE=cloud');
        putenv('ENABLE_WECHAT_LOGIN=true');

        $controller = new WechatController('wechat', Yii::$app->getModule('v1'));
        $response = $controller->actionQrcode();

        $this->assertSame(501, Yii::$app->response->statusCode);
        $this->assertSame('FEATURE_DISABLED', $response['code']);
        $this->assertSame('wechat-login', $response['feature']);
    }

    public function testQrcodeIsDisabledWhenCallbackTokenIsMissing(): void
    {
        putenv('DEPLOYMENT_MODE=cloud');
        putenv('ENABLE_WECHAT_LOGIN=true');
        putenv('WECHAT_APP_ID=wx-app-id');
        putenv('WECHAT_SECRET=wx-secret');

        $controller = new WechatController('wechat', Yii::$app->getModule('v1'));
        $response = $controller->actionQrcode();

        $this->assertSame(501, Yii::$app->response->statusCode);
        $this->assertSame('FEATURE_DISABLED', $response['code']);
    }
}
