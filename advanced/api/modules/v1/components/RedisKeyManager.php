<?php

namespace api\modules\v1\components;

/**
 * Redis 键管理器
 * 
 * 统一管理所有 Redis 缓存键的格式，确保键名一致性和可维护性。
 * 
 * 键格式规范：
 * - 验证码: email:verify:{email}
 * - 验证尝试次数: email:verify:attempts:{email}
 * - 重置令牌: password:reset:{token}
 * - 速率限制: email:ratelimit:{action}:{email}
 * 
 * @author Kiro AI
 * @since 1.0
 */
class RedisKeyManager
{
    /**
     * 验证码缓存键前缀
     */
    const PREFIX_VERIFICATION_CODE = 'email:verify:';
    
    /**
     * 验证尝试次数缓存键前缀
     */
    const PREFIX_VERIFICATION_ATTEMPTS = 'email:verify:attempts:';
    
    /**
     * 重置令牌缓存键前缀
     */
    const PREFIX_RESET_TOKEN = 'password:reset:';
    
    /**
     * 速率限制缓存键前缀
     */
    const PREFIX_RATE_LIMIT = 'email:ratelimit:';
    
    /**
     * 获取验证码缓存键
     * 
     * 格式: email:verify:{email}
     * 用途: 存储邮箱验证码
     * TTL: 900 秒 (15 分钟)
     * 
     * @param string $email 邮箱地址
     * @return string Redis 键
     */
    public static function getVerificationCodeKey(string $email): string
    {
        return self::PREFIX_VERIFICATION_CODE . self::sanitizeEmail($email);
    }
    
    /**
     * 获取验证尝试次数缓存键
     * 
     * 格式: email:verify:attempts:{email}
     * 用途: 记录验证码验证失败次数
     * TTL: 900 秒 (15 分钟)
     * 
     * @param string $email 邮箱地址
     * @return string Redis 键
     */
    public static function getVerificationAttemptsKey(string $email): string
    {
        return self::PREFIX_VERIFICATION_ATTEMPTS . self::sanitizeEmail($email);
    }
    
    /**
     * 获取重置令牌缓存键
     * 
     * 格式: password:reset:{token}
     * 用途: 存储密码重置令牌及相关信息
     * TTL: 1800 秒 (30 分钟)
     * 
     * @param string $token 重置令牌
     * @return string Redis 键
     */
    public static function getResetTokenKey(string $token): string
    {
        return self::PREFIX_RESET_TOKEN . $token;
    }
    
    /**
     * 获取速率限制缓存键
     * 
     * 格式: email:ratelimit:{action}:{email}
     * 用途: 限制特定操作的请求频率
     * TTL: 60 秒 (1 分钟)
     * 
     * @param string $email 邮箱地址
     * @param string $action 操作类型 (如: send_verification, request_reset)
     * @return string Redis 键
     */
    public static function getRateLimitKey(string $email, string $action): string
    {
        return self::PREFIX_RATE_LIMIT . $action . ':' . self::sanitizeEmail($email);
    }
    
    /**
     * 清理邮箱地址，确保键名安全
     * 
     * 将邮箱转换为小写，避免大小写导致的键不一致问题
     * 
     * @param string $email 邮箱地址
     * @return string 清理后的邮箱地址
     */
    protected static function sanitizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
    
    /**
     * 获取所有验证码相关的键（用于批量清理）
     * 
     * @param string $email 邮箱地址
     * @return array 包含验证码和尝试次数的键数组
     */
    public static function getAllVerificationKeys(string $email): array
    {
        return [
            self::getVerificationCodeKey($email),
            self::getVerificationAttemptsKey($email),
        ];
    }
    
    /**
     * 获取所有速率限制键（用于批量清理）
     * 
     * @param string $email 邮箱地址
     * @return array 包含所有速率限制操作的键数组
     */
    public static function getAllRateLimitKeys(string $email): array
    {
        return [
            self::getRateLimitKey($email, 'send_verification'),
            self::getRateLimitKey($email, 'request_reset'),
        ];
    }
}
