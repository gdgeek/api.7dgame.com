<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\models\User;
use api\modules\v1\RefreshToken;
use yii\web\TooManyRequestsHttpException;
use yii\web\BadRequestHttpException;

/**
 * 密码重置服务
 * 
 * 处理密码重置的核心业务逻辑，包括：
 * - 生成和发送重置令牌
 * - 验证重置令牌
 * - 重置密码
 * - 使会话失效
 * 
 * @author Kiro AI
 * @since 1.0
 */
class PasswordResetService extends Component
{
    /**
     * 重置令牌长度
     */
    const TOKEN_LENGTH = 32;
    
    /**
     * 重置令牌过期时间（秒）
     */
    const TOKEN_TTL = 1800; // 30 分钟
    
    /**
     * 发送重置令牌速率限制（秒）
     */
    const RATE_LIMIT_TTL = 60; // 1 分钟
    
    /**
     * @var RateLimiter 速率限制器
     */
    protected $rateLimiter;
    
    /**
     * @var \yii\redis\Connection Redis 连接
     */
    protected $redis;
    
    /**
     * 初始化服务
     */
    public function init()
    {
        parent::init();
        $this->rateLimiter = new RateLimiter();
        $this->redis = Yii::$app->redis;
    }
    
    /**
     * 生成并发送密码重置令牌
     * 
     * @param string $email 邮箱地址
     * @return string 重置令牌
     * @throws BadRequestHttpException 邮箱未验证异常
     * @throws TooManyRequestsHttpException 速率限制异常
     */
    public function sendResetToken(string $email): string
    {
        // 检查邮箱是否已验证
        if (!$this->isEmailVerified($email)) {
            Yii::warning("Password reset requested for unverified email: {$email}", __METHOD__);
            throw new BadRequestHttpException('邮箱未验证，无法重置密码');
        }
        
        // 检查速率限制
        $rateLimitKey = RedisKeyManager::getRateLimitKey($email, 'request_reset');
        if ($this->rateLimiter->tooManyAttempts($rateLimitKey, 1, self::RATE_LIMIT_TTL)) {
            $retryAfter = $this->rateLimiter->availableIn($rateLimitKey);
            throw new TooManyRequestsHttpException("请求过于频繁，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }
        
        // 查找用户
        $user = User::findByEmail($email);
        if ($user === null) {
            Yii::error("User not found for email: {$email}", __METHOD__);
            throw new BadRequestHttpException('用户不存在');
        }
        
        // 生成重置令牌
        $token = $this->generateResetToken();
        
        // 存储到 Redis
        $tokenKey = RedisKeyManager::getResetTokenKey($token);
        $data = json_encode([
            'email' => $email,
            'user_id' => $user->id,
            'created_at' => time(),
        ]);
        $this->redis->executeCommand('SETEX', [$tokenKey, self::TOKEN_TTL, $data]);
        
        // 发送邮件
        $emailService = new EmailService();
        $emailSent = $emailService->sendPasswordResetLink($email, $token);
        
        if (!$emailSent) {
            Yii::warning("Failed to send password reset email to {$email}, but token was stored in Redis", __METHOD__);
            // 即使邮件发送失败，也返回成功，因为令牌已经存储
            // 这样可以避免因邮件服务问题导致整个功能不可用
        }
        
        // 记录速率限制
        $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_TTL);
        
        // 记录日志
        Yii::info("Password reset token sent to {$email}", __METHOD__);
        
        return $token;
    }
    
    /**
     * 验证重置令牌
     * 
     * @param string $token 重置令牌
     * @return bool 令牌是否有效
     */
    public function verifyResetToken(string $token): bool
    {
        $tokenKey = RedisKeyManager::getResetTokenKey($token);
        $storedData = $this->redis->executeCommand('GET', [$tokenKey]);
        
        if ($storedData === null) {
            Yii::warning("Reset token not found or expired: " . substr($token, 0, 8) . "...", __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 重置密码
     * 
     * @param string $token 重置令牌
     * @param string $newPassword 新密码
     * @return bool 是否成功
     * @throws BadRequestHttpException 令牌无效异常
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        // 验证令牌
        $tokenKey = RedisKeyManager::getResetTokenKey($token);
        $storedData = $this->redis->executeCommand('GET', [$tokenKey]);
        
        if ($storedData === null) {
            Yii::warning("Invalid or expired reset token: " . substr($token, 0, 8) . "...", __METHOD__);
            throw new BadRequestHttpException('重置令牌无效或已过期');
        }
        
        $data = json_decode($storedData, true);
        $userId = $data['user_id'] ?? null;
        $email = $data['email'] ?? null;
        
        if ($userId === null) {
            Yii::error("Invalid token data structure", __METHOD__);
            throw new BadRequestHttpException('重置令牌数据无效');
        }
        
        // 查找用户
        $user = User::findIdentity($userId);
        if ($user === null) {
            Yii::error("User not found for ID: {$userId}", __METHOD__);
            throw new BadRequestHttpException('用户不存在');
        }
        
        // 更新密码
        $user->setPassword($newPassword);
        if (!$user->save(false)) {
            Yii::error("Failed to save new password for user ID: {$userId}", __METHOD__);
            return false;
        }
        
        // 删除重置令牌（一次性使用）
        $this->redis->executeCommand('DEL', [$tokenKey]);
        
        // 使所有会话失效
        $this->invalidateUserSessions($userId);
        
        // 记录日志
        Yii::info("Password reset successful for user ID: {$userId}", __METHOD__);
        
        return true;
    }
    
    /**
     * 生成加密的重置令牌
     * 
     * 使用 Yii2 Security 组件生成加密安全的随机令牌
     * 
     * @return string 令牌
     */
    protected function generateResetToken(): string
    {
        return Yii::$app->security->generateRandomString(self::TOKEN_LENGTH);
    }
    
    /**
     * 检查邮箱是否已验证
     * 
     * @param string $email 邮箱地址
     * @return bool 是否已验证
     */
    protected function isEmailVerified(string $email): bool
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            return false;
        }
        
        return $user->isEmailVerified();
    }
    
    /**
     * 使所有用户会话失效
     * 
     * 删除用户的所有 RefreshToken 记录，强制重新登录
     * 
     * @param int $userId 用户 ID
     * @return bool 是否成功
     */
    protected function invalidateUserSessions(int $userId): bool
    {
        try {
            $deletedCount = RefreshToken::deleteAll(['user_id' => $userId]);
            Yii::info("Invalidated {$deletedCount} sessions for user ID: {$userId}", __METHOD__);
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to invalidate sessions for user ID: {$userId}. Error: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
    
    /**
     * 获取令牌信息（用于调试）
     * 
     * @param string $token 重置令牌
     * @return array|null 令牌信息
     */
    public function getTokenInfo(string $token): ?array
    {
        $tokenKey = RedisKeyManager::getResetTokenKey($token);
        $storedData = $this->redis->executeCommand('GET', [$tokenKey]);
        
        if ($storedData === null) {
            return null;
        }
        
        $data = json_decode($storedData, true);
        $ttl = $this->redis->executeCommand('TTL', [$tokenKey]);
        
        return [
            'email' => $data['email'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'ttl' => $ttl > 0 ? $ttl : 0,
            'valid' => $ttl > 0,
        ];
    }
}
