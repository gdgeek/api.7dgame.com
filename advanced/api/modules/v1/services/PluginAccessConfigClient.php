<?php

namespace api\modules\v1\services;

use yii\base\Component;
use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;

/** Reads only the narrow policy endpoint of a deployment-owned configuration service. */
class PluginAccessConfigClient extends Component
{
    public ?string $baseUrl = null;
    private const MAX_RESPONSE_BYTES = 16384;

    public function read(string $pluginId): ?array
    {
        try {
            $base = $this->baseUrl ?? (getenv('PLUGIN_ACCESS_CONFIG_BASE_URL') ?: '');
            $parts = parse_url($base);
            if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                || !preg_match('/^[a-z0-9][a-z0-9.-]*$/iD', $parts['host'] ?? '')
                || isset($parts['user']) || isset($parts['pass'])
                || isset($parts['query']) || isset($parts['fragment'])
                || !in_array($parts['path'] ?? '', ['', '/'], true)
                || (isset($parts['port']) && ($parts['port'] < 1 || $parts['port'] > 65535))
                || !preg_match('/^[a-z0-9][a-z0-9_-]{0,127}$/iD', $pluginId)) {
                throw new \RuntimeException('Invalid configuration request.');
            }
            [$status, $body] = $this->send(rtrim($base, '/') . '/api/v1/plugin/access-config/' . rawurlencode($pluginId));
            if (strlen($body) > self::MAX_RESPONSE_BYTES) {
                throw new \RuntimeException('Configuration response too large.');
            }
            if ($status === 404) {
                return null;
            }
            $response = json_decode($body, true, 16, JSON_THROW_ON_ERROR);
            $data = $response['data'] ?? null;
            if ($status !== 200 || !is_array($response) || ($response['code'] ?? null) !== 0
                || !is_array($data) || ($data['policy_version'] ?? null) !== 1
                || ($data['id'] ?? null) !== $pluginId || ($data['enabled'] ?? null) !== true
                || !in_array($data['access_scope'] ?? null, ['root-only', 'admin-only', 'manager-only', 'auth-only'], true)) {
                throw new \RuntimeException('Invalid configuration response.');
            }
            return ['enabled' => true, 'access_scope' => $data['access_scope']];
        } catch (\Throwable $exception) {
            throw new PluginAccessConfigUnavailableException();
        }
    }

    /** @return array{int, string} */
    protected function send(string $url): array
    {
        $handle = curl_init($url);
        $body = '';
        curl_setopt_array($handle, [
            CURLOPT_HTTPGET => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_PROXY => '',
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
            CURLOPT_TIMEOUT_MS => 5000,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cache-Control: no-cache'],
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > self::MAX_RESPONSE_BYTES) {
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        if (curl_exec($handle) === false) {
            throw new \RuntimeException('Configuration transport failed.');
        }
        return [(int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $body];
    }
}
