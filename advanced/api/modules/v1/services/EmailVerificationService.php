<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\models\User;
use yii\web\TooManyRequestsHttpException;
use yii\web\BadRequestHttpException;

/**
 * 邮箱验证服务
 * 
 * 处理邮箱验证的核心业务逻辑，包括：
 * - 生成和发送验证码
 * - 验证验证码
 * - 速率限制
 * - 失败次数限制和账户锁定
 * 
 * @author Kiro AI
 * @since 1.0
 */
class EmailVerificationService extends Component
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
     * 最大验证失败次数
     */
    const MAX_ATTEMPTS = 5;
    
    /**
     * 锁定时间（秒）
     */
    const LOCK_TTL = 900; // 15 分钟
    
    /**
     * 发送验证码速率限制（秒）
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
     * 生成并发送验证码
     * 
     * @param string $email 邮箱地址
     * @return bool 是否成功
     * @throws TooManyRequestsHttpException 速率限制异常
     */
    public function sendVerificationCode(string $email): bool
    {
        // 检查速率限制
        $rateLimitKey = RedisKeyManager::getRateLimitKey($email, 'send_verification');
        if ($this->rateLimiter->tooManyAttempts($rateLimitKey, 1, self::RATE_LIMIT_TTL)) {
            $retryAfter = $this->rateLimiter->availableIn($rateLimitKey);
            throw new TooManyRequestsHttpException("请求过于频繁，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }
        
        // 生成验证码
        $code = $this->generateVerificationCode();
        
        // 存储到 Redis
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $data = json_encode([
            'code' => $code,
            'created_at' => time(),
        ]);
        $this->redis->executeCommand('SETEX', [$codeKey, self::CODE_TTL, $data]);
        
        // 发送邮件
        $emailService = new EmailService();
        $emailSent = $emailService->sendVerificationCode($email, $code);
        
        if (!$emailSent) {
            Yii::warning("Failed to send verification email to {$email}, but code was stored in Redis", __METHOD__);
            // 即使邮件发送失败，也返回成功，因为验证码已经存储
            // 这样可以避免因邮件服务问题导致整个功能不可用
        }
        
        // 记录速率限制
        $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_TTL);
        
        // 记录日志
        Yii::info("Email verification code sent to {$email}", __METHOD__);
        
        return true;
    }
    
    /**
     * 验证验证码
     * 
     * @param string $email 邮箱地址
     * @param string $code 验证码
     * @return bool 是否验证成功
     * @throws BadRequestHttpException 验证码无效异常
     * @throws TooManyRequestsHttpException 账户锁定异常
     */
    public function verifyCode(string $email, string $code): bool
    {
        // 检查是否被锁定
        if ($this->isLocked($email)) {
            $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
            $retryAfter = $this->rateLimiter->availableIn($attemptsKey);
            Yii::warning("Account locked for {$email} (too many failed attempts)", __METHOD__);
            throw new TooManyRequestsHttpException("验证失败次数过多，账户已被锁定，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }
        
        // 获取存储的验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        
        if ($storedData === null) {
            Yii::warning("Verification code not found or expired for {$email}", __METHOD__);
            throw new BadRequestHttpException('验证码不存在或已过期');
        }
        
        $data = json_decode($storedData, true);
        $storedCode = $data['code'] ?? null;
        
        // 验证验证码
        if ($storedCode !== $code) {
            // 增加失败次数
            $attempts = $this->incrementAttempts($email);
            Yii::warning("Email verification failed for {$email} (attempt {$attempts}/" . self::MAX_ATTEMPTS . ")", __METHOD__);
            throw new BadRequestHttpException('验证码不正确');
        }
        
        // 验证成功，标记邮箱为已验证
        $this->markEmailAsVerified($email);
        
        // 清理 Redis 数据
        $this->clearVerificationData($email);
        
        // 记录日志
        Yii::info("Email verification successful for {$email}", __METHOD__);
        
        return true;
    }
    
    /**
     * 生成 6 位数字验证码
     * 
     * 使用 Yii2 Security 组件生成加密安全的随机数
     * 
     * @return string 验证码
     */
    protected function generateVerificationCode(): string
    {
        // 使用加密安全的随机数生成器
        $randomBytes = Yii::$app->security->generateRandomString(self::CODE_LENGTH);
        
        // 将随机字符串转换为数字
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= ord($randomBytes[$i]) % 10;
        }
        
        return $code;
    }
    
    /**
     * 检查验证尝试次数
     * 
     * @param string $email 邮箱地址
     * @return bool 是否被锁定
     */
    protected function isLocked(string $email): bool
    {
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
        return $this->rateLimiter->tooManyAttempts($attemptsKey, self::MAX_ATTEMPTS, self::LOCK_TTL);
    }
    
    /**
     * 增加验证失败次数
     * 
     * @param string $email 邮箱地址
     * @return int 当前失败次数
     */
    protected function incrementAttempts(string $email): int
    {
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
        return $this->rateLimiter->hit($attemptsKey, self::LOCK_TTL);
    }
    
    /**
     * 标记邮箱为已验证并更新当前用户邮箱
     * 
     * @param string $email 邮箱地址
     * @return bool 是否成功
     */
    protected function markEmailAsVerified(string $email): bool
    {
        // 检查是否是控制台应用或用户组件不存在
        if (Yii::$app instanceof \yii\console\Application || !isset(Yii::$app->user)) {
            Yii::warning("Cannot mark email as verified: not in web application context", __METHOD__);
            return false;
        }
        
        // 获取当前登录用户
        $userId = Yii::$app->user->id;
        if (!$userId) {
            Yii::error("User not logged in", __METHOD__);
            return false;
        }
        
        $user = User::findOne($userId);
        if ($user === null) {
            Yii::error("User not found: {$userId}", __METHOD__);
            return false;
        }
        
        // 更新用户邮箱和验证状态
        $user->email = $email;
        $user->email_verified_at = time();
        
        if (!$user->save(false)) {
            Yii::error("Failed to update user email: " . json_encode($user->errors), __METHOD__);
            return false;
        }
        
        Yii::info("Email verified and updated for user {$userId}: {$email}", __METHOD__);
        return true;
    }
    
    /**
     * 清理验证相关的 Redis 数据
     * 
     * @param string $email 邮箱地址
     * @return bool 是否成功
     */
    protected function clearVerificationData(string $email): bool
    {
        $keys = RedisKeyManager::getAllVerificationKeys($email);
        $result = $this->rateLimiter->clearMany($keys);
        return $result > 0;
    }
}
