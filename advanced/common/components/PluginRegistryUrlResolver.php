<?php

namespace common\components;

use InvalidArgumentException;

class PluginRegistryUrlResolver
{
    private const PLUGIN_CONFIG = [
        'user-management' => [
            'env' => 'PLUGIN_USER_MANAGEMENT_URL',
            'production' => 'https://user-manager.plugins.xrugc.com',
            'development' => 'https://user-manager.d.plugins.xrugc.com',
        ],
        'system-admin' => [
            'env' => 'PLUGIN_SYSTEM_ADMIN_URL',
            'production' => 'https://system-admin.plugins.xrugc.com',
            'development' => 'https://system-admin.d.plugins.xrugc.com',
        ],
    ];

    public static function forPlugin(string $pluginId): array
    {
        $config = self::PLUGIN_CONFIG[$pluginId] ?? null;
        if ($config === null) {
            throw new InvalidArgumentException("Unknown plugin registry config: {$pluginId}");
        }

        $baseUrl = self::resolveBaseUrl($config);

        return [
            'url' => $baseUrl . '/',
            'allowed_origin' => $baseUrl,
        ];
    }

    public static function productionDefaults(string $pluginId): array
    {
        $config = self::PLUGIN_CONFIG[$pluginId] ?? null;
        if ($config === null) {
            throw new InvalidArgumentException("Unknown plugin registry config: {$pluginId}");
        }

        return [
            'url' => $config['production'] . '/',
            'allowed_origin' => $config['production'],
        ];
    }

    private static function resolveBaseUrl(array $config): string
    {
        $envName = $config['env'];
        $envValue = getenv($envName);
        if (is_string($envValue) && trim($envValue) !== '') {
            return rtrim(trim($envValue), '/');
        }

        return self::isDevelopmentEnvironment()
            ? $config['development']
            : $config['production'];
    }

    private static function isDevelopmentEnvironment(): bool
    {
        $appEnv = strtolower(trim((string) (getenv('APP_ENV') ?: 'production')));

        return in_array($appEnv, ['development', 'develop', 'dev', 'local', 'test'], true);
    }
}
