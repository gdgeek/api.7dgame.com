<?php

namespace common\components;

/**
 * UUID 生成辅助类
 * 提供 UUID v4 生成功能
 */
class UuidHelper
{
    /**
     * 生成 UUID v4
     * 
     * @return string UUID 字符串，格式：xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     */
    public static function uuid()
    {
        // 生成 16 字节的随机数据
        $data = random_bytes(16);

        // 设置版本号 (0100xxxx)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        
        // 设置变体 (10xxxxxx)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // 格式化为 UUID 字符串
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
