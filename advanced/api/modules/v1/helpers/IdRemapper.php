<?php

namespace api\modules\v1\helpers;

/**
 * ID 重映射工具类
 *
 * 递归遍历 JSON 数据树，根据替换规则将指定 key 的值替换为映射表中的新值。
 * 纯静态工具类，无状态，便于单元测试。
 */
class IdRemapper
{
    /**
     * 递归遍历 JSON 数据树，替换指定 key 的值
     *
     * @param mixed $data JSON 解码后的数据（数组或标量）
     * @param array $replacements 替换规则数组，格式：
     *   [
     *     ['key' => 'meta_id', 'map' => [旧ID => 新ID, ...], 'numericOnly' => false],
     *     ['key' => 'resource', 'map' => [旧ID => 新ID, ...], 'numericOnly' => true],
     *   ]
     * @return mixed 替换后的数据
     */
    public static function remap(mixed $data, array $replacements): mixed
    {
        // If data is not an array, return it as-is (scalar values, null, etc.)
        if (!is_array($data)) {
            return $data;
        }

        // Build a lookup index from replacements for efficient matching
        $rules = [];
        foreach ($replacements as $replacement) {
            $key = $replacement['key'];
            $rules[$key] = [
                'map' => $replacement['map'] ?? [],
                'numericOnly' => $replacement['numericOnly'] ?? false,
            ];
        }

        return self::remapRecursive($data, $rules);
    }

    /**
     * 递归遍历数组并执行替换
     *
     * @param array $data 当前层级的数据
     * @param array $rules 索引化的替换规则
     * @return array 替换后的数据
     */
    private static function remapRecursive(array $data, array $rules): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively process nested arrays
                $result[$key] = self::remapRecursive($value, $rules);
            } elseif (is_string($key) && isset($rules[$key])) {
                // Current key matches a replacement rule
                $rule = $rules[$key];

                if ($rule['numericOnly'] && !self::isNumericValue($value)) {
                    // numericOnly is true but value is not numeric — skip replacement
                    $result[$key] = $value;
                } else {
                    // Attempt replacement using the map
                    $result[$key] = self::replaceValue($value, $rule['map']);
                }
            } else {
                // No matching rule, keep value as-is
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * 检查值是否为数字类型（int 或 float）
     *
     * @param mixed $value 要检查的值
     * @return bool
     */
    private static function isNumericValue(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }

    /**
     * 使用映射表替换值
     *
     * 支持 int 和 string 类型的键查找。
     * 如果值在映射表中找到对应的新值，则返回新值；否则返回原值。
     *
     * @param mixed $value 原始值
     * @param array $map 旧值到新值的映射表
     * @return mixed 替换后的值（或原值）
     */
    private static function replaceValue(mixed $value, array $map): mixed
    {
        // Only int and string values can be used as array keys in PHP
        if (!is_int($value) && !is_string($value)) {
            return $value;
        }

        // Try direct lookup (works for both int and string keys)
        if (array_key_exists($value, $map)) {
            return $map[$value];
        }

        return $value;
    }
}
