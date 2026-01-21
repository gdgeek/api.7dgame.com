<?php

namespace api\modules\v1\components;

use Yii;
use yii\base\Component;

/**
 * 速率限制器
 * 
 * 使用 Redis 实现速率限制功能，防止滥用和暴力破解。
 * 
 * 功能：
 * - 检查是否超过速率限制
 * - 增加尝试次数
 * - 获取剩余重试时间
 * - 清除限制
 * 
 * 使用场景：
 * - 发送验证码：1 分钟内只能发送 1 次
 * - 请求密码重置：1 分钟内只能请求 1 次
 * - 验证码验证：15 分钟内最多失败 5 次
 * 
 * @author Kiro AI
 * @since 1.0
 */
class RateLimiter extends Component
{
    /**
     * @var \yii\redis\Connection Redis 连接组件
     */
    protected $redis;
    
    /**
     * 初始化组件
     */
    public function init()
    {
        parent::init();
        $this->redis = Yii::$app->redis;
    }
    
    /**
     * 检查是否超过速率限制
     * 
     * @param string $key 限制键
     * @param int $maxAttempts 最大尝试次数
     * @param int $decaySeconds 衰减时间（秒）
     * @return bool 是否超过限制（true = 超过限制，false = 未超过）
     */
    public function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $attempts = $this->attempts($key);
        
        return $attempts >= $maxAttempts;
    }
    
    /**
     * 增加尝试次数
     * 
     * @param string $key 限制键
     * @param int $decaySeconds 衰减时间（秒）
     * @return int 当前尝试次数
     */
    public function hit(string $key, int $decaySeconds): int
    {
        // 增加计数
        $this->redis->executeCommand('INCR', [$key]);
        
        // 获取当前 TTL
        $ttl = $this->redis->executeCommand('TTL', [$key]);
        
        // 如果键没有过期时间（-1）或键不存在（-2），设置过期时间
        if ($ttl < 0) {
            $this->redis->executeCommand('EXPIRE', [$key, $decaySeconds]);
        }
        
        return $this->attempts($key);
    }
    
    /**
     * 获取当前尝试次数
     * 
     * @param string $key 限制键
     * @return int 当前尝试次数
     */
    public function attempts(string $key): int
    {
        $value = $this->redis->executeCommand('GET', [$key]);
        return $value !== null ? (int)$value : 0;
    }
    
    /**
     * 获取剩余重试时间（秒）
     * 
     * @param string $key 限制键
     * @return int 剩余秒数（0 表示可以重试）
     */
    public function availableIn(string $key): int
    {
        $ttl = $this->redis->executeCommand('TTL', [$key]);
        
        // TTL 返回值：
        // -2: 键不存在
        // -1: 键存在但没有设置过期时间
        // > 0: 剩余秒数
        
        return $ttl > 0 ? $ttl : 0;
    }
    
    /**
     * 清除限制
     * 
     * @param string $key 限制键
     * @return bool 是否成功
     */
    public function clear(string $key): bool
    {
        $result = $this->redis->executeCommand('DEL', [$key]);
        return $result > 0;
    }
    
    /**
     * 重置尝试次数（将计数设置为 0，但保留 TTL）
     * 
     * @param string $key 限制键
     * @return bool 是否成功
     */
    public function resetAttempts(string $key): bool
    {
        // 获取当前 TTL
        $ttl = $this->redis->executeCommand('TTL', [$key]);
        
        if ($ttl > 0) {
            // 如果有 TTL，设置为 0 并保留 TTL
            $this->redis->executeCommand('SET', [$key, 0, 'EX', $ttl]);
            return true;
        } else {
            // 如果没有 TTL，直接删除键
            return $this->clear($key);
        }
    }
    
    /**
     * 批量清除多个限制键
     * 
     * @param array $keys 限制键数组
     * @return int 成功删除的键数量
     */
    public function clearMany(array $keys): int
    {
        if (empty($keys)) {
            return 0;
        }
        
        $result = $this->redis->executeCommand('DEL', $keys);
        return (int)$result;
    }
    
    /**
     * 检查键是否存在
     * 
     * @param string $key 限制键
     * @return bool 是否存在
     */
    public function exists(string $key): bool
    {
        $result = $this->redis->executeCommand('EXISTS', [$key]);
        return $result > 0;
    }
    
    /**
     * 获取键的详细信息（用于调试）
     * 
     * @param string $key 限制键
     * @return array 包含 attempts, ttl, exists 的数组
     */
    public function getInfo(string $key): array
    {
        return [
            'key' => $key,
            'attempts' => $this->attempts($key),
            'ttl' => $this->availableIn($key),
            'exists' => $this->exists($key),
        ];
    }
}
