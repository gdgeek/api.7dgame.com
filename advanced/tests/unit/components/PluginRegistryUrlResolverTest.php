<?php

namespace tests\unit\components;

use common\components\PluginRegistryUrlResolver;
use PHPUnit\Framework\TestCase;

class PluginRegistryUrlResolverTest extends TestCase
{
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->saveEnv([
            'APP_ENV',
            'PLUGIN_USER_MANAGEMENT_URL',
            'PLUGIN_SYSTEM_ADMIN_URL',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $name => $value) {
            if ($value === null) {
                putenv($name);
                continue;
            }

            putenv("{$name}={$value}");
        }

        parent::tearDown();
    }

    public function testDevelopmentEnvironmentUsesDevelopPluginDomains(): void
    {
        putenv('APP_ENV=development');
        putenv('PLUGIN_USER_MANAGEMENT_URL');
        putenv('PLUGIN_SYSTEM_ADMIN_URL');

        $userManagement = PluginRegistryUrlResolver::forPlugin('user-management');
        $systemAdmin = PluginRegistryUrlResolver::forPlugin('system-admin');

        $this->assertSame('https://user-manager.d.plugins.xrugc.com/', $userManagement['url']);
        $this->assertSame('https://user-manager.d.plugins.xrugc.com', $userManagement['allowed_origin']);
        $this->assertSame('https://system-admin.d.plugins.xrugc.com/', $systemAdmin['url']);
        $this->assertSame('https://system-admin.d.plugins.xrugc.com', $systemAdmin['allowed_origin']);
    }

    public function testExplicitPluginUrlEnvironmentVariablesOverrideDefaults(): void
    {
        putenv('APP_ENV=production');
        putenv('PLUGIN_USER_MANAGEMENT_URL=https://user-manager.custom.example.com');
        putenv('PLUGIN_SYSTEM_ADMIN_URL=https://system-admin.custom.example.com/');

        $userManagement = PluginRegistryUrlResolver::forPlugin('user-management');
        $systemAdmin = PluginRegistryUrlResolver::forPlugin('system-admin');

        $this->assertSame('https://user-manager.custom.example.com/', $userManagement['url']);
        $this->assertSame('https://user-manager.custom.example.com', $userManagement['allowed_origin']);
        $this->assertSame('https://system-admin.custom.example.com/', $systemAdmin['url']);
        $this->assertSame('https://system-admin.custom.example.com', $systemAdmin['allowed_origin']);
    }

    public function testProductionDefaultsRemainStableWithoutOverrides(): void
    {
        putenv('APP_ENV=production');
        putenv('PLUGIN_USER_MANAGEMENT_URL');
        putenv('PLUGIN_SYSTEM_ADMIN_URL');

        $userManagement = PluginRegistryUrlResolver::forPlugin('user-management');
        $systemAdmin = PluginRegistryUrlResolver::forPlugin('system-admin');

        $this->assertSame('https://user-manager.plugins.xrugc.com/', $userManagement['url']);
        $this->assertSame('https://user-manager.plugins.xrugc.com', $userManagement['allowed_origin']);
        $this->assertSame('https://system-admin.plugins.xrugc.com/', $systemAdmin['url']);
        $this->assertSame('https://system-admin.plugins.xrugc.com', $systemAdmin['allowed_origin']);
    }

    private function saveEnv(array $names): void
    {
        foreach ($names as $name) {
            $value = getenv($name);
            $this->originalEnv[$name] = $value === false ? null : $value;
        }
    }
}
