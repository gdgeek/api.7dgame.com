<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\SystemController;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Response;

final class SystemDeploymentTest extends TestCase
{
    private array $originalEnv = [];
    private $originalResponseComponent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalResponseComponent = Yii::$app->get('response', false);
        Yii::$app->set('response', new Response());
        $this->originalEnv = [];
        foreach ($this->deploymentEnvNames() as $name) {
            $value = getenv($name);
            $this->originalEnv[$name] = $value === false ? null : $value;
            putenv($name);
        }
        Yii::$app->response->headers->removeAll();
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $name => $value) {
            if ($value === null) {
                putenv($name);
            } else {
                putenv($name . '=' . $value);
            }
        }
        Yii::$app->set('response', $this->originalResponseComponent);
        parent::tearDown();
    }

    public function testDeploymentDefaultsToCloud(): void
    {
        $data = $this->deployment();

        $this->assertSame('cloud', $data['deploymentMode']);
        $this->assertSame('cos', $data['storageDriver']);
        $this->assertTrue($data['features']['cosStorage']);
        $this->assertSame('no-store', Yii::$app->response->headers->get('Cache-Control'));
        $this->assertFalse($data['features']['wechatLogin']);
    }

    public function testWechatLoginRequiresFlagAndCredentials(): void
    {
        putenv('ENABLE_WECHAT_LOGIN=true');
        putenv('WECHAT_APP_ID=wx-app-id');
        putenv('WECHAT_SECRET=wx-secret');
        putenv('WECHAT_TOKEN=wx-token');

        $this->assertTrue($this->deployment()['features']['wechatLogin']);

        putenv('WECHAT_SECRET');
        $this->assertFalse($this->deployment()['features']['wechatLogin']);

        putenv('WECHAT_SECRET=wx-secret');
        putenv('WECHAT_TOKEN');
        $this->assertFalse($this->deployment()['features']['wechatLogin']);
    }

    public function testLocalDeploymentUsesLocalStorageDefaults(): void
    {
        putenv('DEPLOYMENT_MODE=local');

        $data = $this->deployment();

        $this->assertSame('local', $data['deploymentMode']);
        $this->assertSame('local', $data['storageDriver']);
        $this->assertSame('/storage', $data['storage']['publicBaseUrl']);
        $this->assertSame('store', $data['storage']['publicBucket']);
        $this->assertSame('raw', $data['storage']['privateBucket']);
        $this->assertSame('temp', $data['storage']['tempBucket']);
        $this->assertFalse($data['features']['cosStorage']);
        $this->assertFalse($data['features']['ai3dGenerator']);
    }

    public function testPrivateDeploymentAliasesToLocal(): void
    {
        putenv('DEPLOYMENT_MODE=private');

        $data = $this->deployment();

        $this->assertSame('local', $data['deploymentMode']);
        $this->assertSame('local', $data['storageDriver']);
    }

    public function testExplicitStorageDriverOverridesModeDerivedDefault(): void
    {
        putenv('DEPLOYMENT_MODE=local');
        putenv('FILE_STORAGE_DRIVER=cos');

        $data = $this->deployment();

        $this->assertSame('local', $data['deploymentMode']);
        $this->assertSame('cos', $data['storageDriver']);
    }

    public function testDeploymentResponseDoesNotExposeSecretsOrStorageRoots(): void
    {
        putenv('DEPLOYMENT_MODE=local');
        putenv('SECRET_ID=secret-id');
        putenv('SECRET_KEY=secret-key');
        putenv('LOCAL_STORAGE_ROOT=/host/storage');
        putenv('LOCAL_STORAGE_CONTAINER_ROOT=/container/storage');

        $encoded = json_encode($this->deployment());

        $this->assertIsString($encoded);
        $this->assertStringNotContainsString('SECRET_ID', $encoded);
        $this->assertStringNotContainsString('secret-id', $encoded);
        $this->assertStringNotContainsString('SECRET_KEY', $encoded);
        $this->assertStringNotContainsString('secret-key', $encoded);
        $this->assertStringNotContainsString('LOCAL_STORAGE_ROOT', $encoded);
        $this->assertStringNotContainsString('/host/storage', $encoded);
        $this->assertStringNotContainsString('LOCAL_STORAGE_CONTAINER_ROOT', $encoded);
        $this->assertStringNotContainsString('/container/storage', $encoded);
    }

    private function deployment(): array
    {
        $controller = new SystemController('system', Yii::$app->getModule('v1'));
        return $controller->actionDeployment();
    }

    private function deploymentEnvNames(): array
    {
        return [
            'DEPLOYMENT_MODE',
            'FILE_STORAGE_DRIVER',
            'LOCAL_STORAGE_PUBLIC_BASE_URL',
            'LOCAL_STORAGE_PUBLIC_BUCKET',
            'LOCAL_STORAGE_PRIVATE_BUCKET',
            'LOCAL_STORAGE_TEMP_BUCKET',
            'ENABLE_COS_STORAGE',
            'ENABLE_COS_IMAGE_PROCESSING',
            'ENABLE_AI_3D_GENERATOR',
            'ENABLE_WECHAT_LOGIN',
            'WECHAT_APP_ID',
            'WECHAT_APPID',
            'WECHAT_SECRET',
            'WECHAT_TOKEN',
            'ENABLE_APPLE_LOGIN',
            'ENABLE_GEO_IP_LOCALE',
            'ENABLE_ANALYTICS',
            'ENABLE_EXTERNAL_CDN',
            'SECRET_ID',
            'SECRET_KEY',
            'LOCAL_STORAGE_ROOT',
            'LOCAL_STORAGE_CONTAINER_ROOT',
        ];
    }
}
