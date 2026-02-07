<?php

namespace common\components\security;

use yii\log\Target;

/**
 * LogFilter 组件 - 过滤日志中的敏感信息
 *
 * 在日志写入前自动检测并替换敏感数据模式，
 * 防止密码、令牌、API 密钥等信息泄露到日志文件中。
 *
 * 使用方式：在日志目标配置中集成此过滤器
 */
class LogFilter
{
    /**
     * 替换占位符
     */
    const FILTERED = '[FILTERED]';

    /**
     * 敏感字段名模式（不区分大小写）
     */
    private static $sensitiveKeys = [
        'password',
        'passwd',
        'pwd',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'apikey',
        'secret',
        'secret_key',
        'secret_id',
        'card_number',
        'cvv',
        'ccv',
        'ssn',
        'passport_number',
        'authorization',
        'auth_code',
        'mailer_password',
        'mysql_password',
        'mysql_root_password',
        'wechat_secret',
        'wechat_aes_key',
        'private_key',
        'signing_key',
    ];

    /**
     * 敏感值的正则模式
     */
    private static $sensitivePatterns = [
        // Bearer tokens
        '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i' => 'Bearer ' . self::FILTERED,
        // JWT tokens (xxx.xxx.xxx)
        '/eyJ[A-Za-z0-9_-]+\.eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+/' => self::FILTERED,
        // Email passwords in DSN-like strings
        '/password=([^\s&;]+)/i' => 'password=' . self::FILTERED,
    ];

    /**
     * 过滤日志消息中的敏感信息
     *
     * @param string $message 原始日志消息
     * @return string 过滤后的消息
     */
    public static function filter(string $message): string
    {
        // 1. 过滤键值对中的敏感字段
        $message = self::filterKeyValuePairs($message);

        // 2. 过滤已知的敏感值模式
        $message = self::filterPatterns($message);

        return $message;
    }

    /**
     * 过滤数组中的敏感字段
     *
     * @param array $data 原始数据
     * @return array 过滤后的数据
     */
    public static function filterArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                $data[$key] = self::FILTERED;
            } elseif (is_array($value)) {
                $data[$key] = self::filterArray($value);
            } elseif (is_string($value)) {
                $data[$key] = self::filter($value);
            }
        }
        return $data;
    }

    /**
     * 检查键名是否为敏感字段
     */
    public static function isSensitiveKey(string $key): bool
    {
        $lowerKey = strtolower($key);
        foreach (self::$sensitiveKeys as $sensitiveKey) {
            if (strpos($lowerKey, $sensitiveKey) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 过滤键值对格式的敏感数据
     * 支持格式: "key" => "value", "key": "value", key=value
     */
    private static function filterKeyValuePairs(string $message): string
    {
        $keysPattern = implode('|', array_map(function ($key) {
            return preg_quote($key, '/');
        }, self::$sensitiveKeys));

        // 匹配 "key" => "value" 或 'key' => 'value'
        $message = preg_replace(
            '/(["\']?)(' . $keysPattern . ')\\1\s*=>\s*(["\'])([^"\']*?)\\3/i',
            '"$2" => "$3' . self::FILTERED . '$3"',
            $message
        );

        // 匹配 "key": "value" (JSON 格式)
        $message = preg_replace(
            '/"(' . $keysPattern . ')"\s*:\s*"([^"]*?)"/i',
            '"$1": "' . self::FILTERED . '"',
            $message
        );

        // 匹配 key=value (URL 参数或配置格式)
        $message = preg_replace(
            '/\b(' . $keysPattern . ')=([^\s&;,\]}\)]+)/i',
            '$1=' . self::FILTERED,
            $message
        );

        return $message;
    }

    /**
     * 过滤已知的敏感值模式
     */
    private static function filterPatterns(string $message): string
    {
        foreach (self::$sensitivePatterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }
        return $message;
    }
}
