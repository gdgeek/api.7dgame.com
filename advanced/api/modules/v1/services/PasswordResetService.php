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
     * 验证码长度
     */
    const CODE_LENGTH = 6;
    
    /**
     * 验证码过期时间（秒）
     */
    const CODE_TTL = 900; // 15 分钟
    
    /**
     * 发送重置令牌速率限制（秒）
     */
    const RATE_LIMIT_TTL = 60; // 1 分钟

    /**
     * 最大验证失败次数
     */
    const MAX_ATTEMPTS = 5;

    /**
     * 锁定时间（秒）
     */
    const LOCK_TTL = 900; // 15 分钟
    
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
     * 生成并发送密码重置验证码
     * 
     * @param string $email 邮箱地址
     * @return string 验证码
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
        
        // 生成验证码
        $code = $this->generateResetCode();
        
        // 存储到 Redis
        $codeKey = RedisKeyManager::getResetCodeKey($email);
        $data = json_encode([
            'code' => $code,
            'email' => $email,
            'user_id' => $user->id,
            'created_at' => time(),
        ]);
        $this->redis->executeCommand('SETEX', [$codeKey, self::CODE_TTL, $data]);
        
        // 发送邮件
        $emailService = new EmailService();
        $emailSent = $emailService->sendPasswordResetCode($email, $code);
        
        if (!$emailSent) {
            Yii::warning("Failed to send password reset email to {$email}, but code was stored in Redis", __METHOD__);
            // 即使邮件发送失败，也返回成功，因为验证码已经存储
            // 这样可以避免因邮件服务问题导致整个功能不可用
        }
        
        // 记录速率限制
        $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_TTL);
        
        // 记录日志
        Yii::info("Password reset code sent to {$email}", __METHOD__);
        
        return $code;
    }
    
    /**
     * 验证重置验证码
     * 
     * @param string $email 邮箱地址
     * @param string $code 验证码
     * @return bool
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    public function verifyResetCode(string $email, string $code): bool
    {
        if ($this->isCodeLocked($email)) {
            $attemptsKey = RedisKeyManager::getResetAttemptsKey($email);
            $retryAfter = $this->rateLimiter->availableIn($attemptsKey);
            throw new TooManyRequestsHttpException("验证失败次数过多，账户已被锁定，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }

        $codeKey = RedisKeyManager::getResetCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        
        if ($storedData === null) {
            throw new BadRequestHttpException('验证码不存在或已过期');
        }

        $data = json_decode($storedData, true);
        $storedCode = $data['code'] ?? null;
        if ($storedCode !== $code) {
            $attempts = $this->incrementCodeAttempts($email);
            Yii::warning("Password reset code verify failed for {$email} (attempt {$attempts}/" . self::MAX_ATTEMPTS . ")", __METHOD__);
            throw new BadRequestHttpException('验证码不正确');
        }

        return true;
    }
    
    /**
     * 重置密码
     * 
     * @param string $email 邮箱地址
     * @param string $code 验证码
     * @param string $newPassword 新密码
     * @return bool 是否成功
     * @throws BadRequestHttpException 验证码无效异常
     * @throws TooManyRequestsHttpException
     */
    public function resetPassword(string $email, string $code, string $newPassword): bool
    {
        // 验证验证码
        $this->verifyResetCode($email, $code);

        // 查找用户
        $user = User::findByEmail($email);
        if ($user === null) {
            Yii::error("User not found for email: {$email}", __METHOD__);
            throw new BadRequestHttpException('用户不存在');
        }
        
        // 更新密码
        $user->setPassword($newPassword);
        if (!$user->save(false)) {
            Yii::error("Failed to save new password for user ID: {$user->id}", __METHOD__);
            return false;
        }
        
        // 删除验证码（一次性使用）并清理失败计数
        $this->clearResetCodeData($email);
        
        // 使所有会话失效
        $this->invalidateUserSessions((int)$user->id);
        
        // 记录日志
        Yii::info("Password reset successful for user ID: {$user->id}", __METHOD__);
        
        return true;
    }

    /**
     * 登录态修改密码（需邮箱已验证）
     *
     * @param User $user 当前登录用户
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return bool
     * @throws BadRequestHttpException
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): bool
    {
        if (!$user->isEmailVerified()) {
            throw new BadRequestHttpException('邮箱未验证，请先完成邮箱验证后再修改密码');
        }

        if (!$user->validatePassword($oldPassword)) {
            throw new BadRequestHttpException('旧密码不正确');
        }

        if ($oldPassword === $newPassword) {
            throw new BadRequestHttpException('新密码不能与旧密码相同');
        }

        $user->setPassword($newPassword);
        if (!$user->save(false, ['password_hash'])) {
            Yii::error("Failed to change password for user ID: {$user->id}", __METHOD__);
            return false;
        }

        // 修改密码后使会话失效，强制重新登录
        $this->invalidateUserSessions((int)$user->id);
        Yii::info("Password changed successfully for user ID: {$user->id}", __METHOD__);

        return true;
    }
    
    /**
     * 生成 6 位数字验证码
     * 
     * 使用 Yii2 Security 组件生成加密安全的随机数
     * 
     * @return string 验证码
     */
    protected function generateResetCode(): string
    {
        $randomBytes = Yii::$app->security->generateRandomString(self::CODE_LENGTH);
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= ord($randomBytes[$i]) % 10;
        }
        return $code;
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
     * 检查验证码是否被锁定
     *
     * @param string $email
     * @return bool
     */
    protected function isCodeLocked(string $email): bool
    {
        $attemptsKey = RedisKeyManager::getResetAttemptsKey($email);
        return $this->rateLimiter->tooManyAttempts($attemptsKey, self::MAX_ATTEMPTS, self::LOCK_TTL);
    }

    /**
     * 增加验证码失败次数
     *
     * @param string $email
     * @return int
     */
    protected function incrementCodeAttempts(string $email): int
    {
        $attemptsKey = RedisKeyManager::getResetAttemptsKey($email);
        return $this->rateLimiter->hit($attemptsKey, self::LOCK_TTL);
    }

    /**
     * 清理找回密码验证码数据
     *
     * @param string $email
     * @return void
     */
    protected function clearResetCodeData(string $email): void
    {
        $keys = [
            RedisKeyManager::getResetCodeKey($email),
            RedisKeyManager::getResetAttemptsKey($email),
        ];
        $this->rateLimiter->clearMany($keys);
    }
}
