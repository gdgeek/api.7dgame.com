<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\models\User;
use yii\web\TooManyRequestsHttpException;
use yii\web\BadRequestHttpException;
use yii\db\IntegrityException;

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
     * 变更邮箱确认令牌有效期（秒）
     */
    const CHANGE_TOKEN_TTL = 600; // 10 分钟
    
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
    public function sendVerificationCode(string $email, string $locale = 'en-US', array $i18n = []): bool
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
        $emailSent = $emailService->sendVerificationCode($email, $code, $locale, $i18n);
        
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
     * 发送当前绑定邮箱的二次确认验证码（用于改绑/解绑）
     *
     * @param User $user 当前用户
     * @return bool
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    public function sendCurrentEmailConfirmationCode(User $user, string $locale = 'en-US', array $i18n = []): bool
    {
        if (empty($user->email)) {
            throw new BadRequestHttpException('当前账号未绑定邮箱');
        }
        if (!$user->isEmailVerified()) {
            throw new BadRequestHttpException('当前邮箱未验证，无需二次确认，可直接改绑');
        }

        return $this->sendVerificationCode($user->email, $locale, $i18n);
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
    public function verifyCode(string $email, string $code, ?string $changeToken = null): bool
    {
        $this->assertCodeValid($email, $code);
        
        // 验证成功，标记邮箱为已验证
        $this->markEmailAsVerified($email, $changeToken);
        
        // 清理 Redis 数据
        $this->clearVerificationData($email);
        
        // 记录日志
        Yii::info("Email verification successful for {$email}", __METHOD__);
        
        return true;
    }

    /**
     * 校验当前绑定邮箱验证码并签发邮箱变更令牌
     *
     * @param User $user 当前用户
     * @param string $code 验证码
     * @return string 变更令牌
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    public function verifyCurrentEmailForChange(User $user, string $code): string
    {
        if (empty($user->email) || !$user->isEmailVerified()) {
            throw new BadRequestHttpException('当前账号未绑定已验证邮箱');
        }

        $currentEmail = $user->email;
        $this->assertCodeValid($currentEmail, $code);
        $this->clearVerificationData($currentEmail);

        $changeToken = Yii::$app->security->generateRandomString(48);
        $changeTokenKey = RedisKeyManager::getEmailChangeTokenKey((int)$user->id);
        $this->redis->executeCommand('SETEX', [$changeTokenKey, self::CHANGE_TOKEN_TTL, $changeToken]);

        return $changeToken;
    }

    /**
     * 验证当前绑定邮箱验证码并解绑邮箱
     *
     * @param User $user 当前用户
     * @param string $code 验证码
     * @return bool
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    public function unbindCurrentEmail(User $user, ?string $code = null): bool
    {
        if (empty($user->email)) {
            throw new BadRequestHttpException('当前账号未绑定邮箱');
        }

        $currentEmail = $user->email;
        if ($user->isEmailVerified()) {
            if (empty($code)) {
                throw new BadRequestHttpException('当前邮箱已验证，解绑需提供验证码');
            }
            $this->assertCodeValid($currentEmail, $code);
            $this->clearVerificationData($currentEmail);
        }

        $this->clearEmailChangeToken((int)$user->id);

        $user->email = null;
        $user->email_verified_at = null;

        if (!$user->save(false, ['email', 'email_verified_at'])) {
            Yii::error("Failed to unbind email for user {$user->id}", __METHOD__);
            return false;
        }

        Yii::info("Email unbound for user {$user->id}", __METHOD__);
        return true;
    }

    /**
     * 查询发送验证码冷却时间
     *
     * @param string $email 邮箱地址
     * @return array
     */
    public function getSendCooldown(string $email): array
    {
        $rateLimitKey = RedisKeyManager::getRateLimitKey($email, 'send_verification');
        $retryAfter = $this->rateLimiter->availableIn($rateLimitKey);

        return [
            'can_send' => $retryAfter === 0,
            'retry_after' => $retryAfter,
            'limit_seconds' => self::RATE_LIMIT_TTL,
        ];
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
    protected function markEmailAsVerified(string $email, ?string $changeToken = null): bool
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
        
        // 已有验证邮箱时，改绑需要先完成旧邮箱二次确认
        if (!empty($user->email) && $user->email !== $email && $user->isEmailVerified()) {
            $this->assertEmailChangeTokenValid((int)$userId, $changeToken);
        }

        // 更新用户邮箱和验证状态
        $existingUser = User::find()
            ->where(['email' => $email])
            ->andWhere(['!=', 'id', $userId])
            ->one();
        if ($existingUser !== null) {
            throw new BadRequestHttpException('该邮箱已被其他账号绑定');
        }

        $user->email = $email;
        $user->email_verified_at = time();
        
        try {
            if (!$user->save(false, ['email', 'email_verified_at'])) {
                Yii::error("Failed to update user email: " . json_encode($user->errors), __METHOD__);
                return false;
            }
        } catch (IntegrityException $e) {
            // DB UNIQUE(email) 兜底，防止并发下重复绑定
            Yii::warning("Email already bound by another account: {$email}", __METHOD__);
            throw new BadRequestHttpException('该邮箱已被其他账号绑定');
        }
        
        // 绑定或改绑成功后，清理变更令牌
        $this->clearEmailChangeToken((int)$userId);

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

    /**
     * 校验验证码，不进行用户资料写入
     *
     * @param string $email
     * @param string $code
     * @return void
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    protected function assertCodeValid(string $email, string $code): void
    {
        if ($this->isLocked($email)) {
            $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
            $retryAfter = $this->rateLimiter->availableIn($attemptsKey);
            Yii::warning("Account locked for {$email} (too many failed attempts)", __METHOD__);
            throw new TooManyRequestsHttpException("验证失败次数过多，账户已被锁定，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }

        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        if ($storedData === null) {
            Yii::warning("Verification code not found or expired for {$email}", __METHOD__);
            throw new BadRequestHttpException('验证码不存在或已过期');
        }

        $data = json_decode($storedData, true);
        $storedCode = $data['code'] ?? null;

        if ($storedCode !== $code) {
            $attempts = $this->incrementAttempts($email);
            Yii::warning("Email verification failed for {$email} (attempt {$attempts}/" . self::MAX_ATTEMPTS . ")", __METHOD__);
            throw new BadRequestHttpException('验证码不正确');
        }
    }

    /**
     * 检查当前用户的改绑确认令牌是否有效
     *
     * @param int $userId
     * @throws BadRequestHttpException
     */
    protected function assertEmailChangeTokenValid(int $userId, ?string $requestToken): void
    {
        if (empty($requestToken)) {
            throw new BadRequestHttpException('改绑邮箱需先完成旧邮箱验证');
        }

        $key = RedisKeyManager::getEmailChangeTokenKey($userId);
        $storedToken = (string)$this->redis->executeCommand('GET', [$key]);
        if ($storedToken === '' || !hash_equals($storedToken, $requestToken)) {
            throw new BadRequestHttpException('改绑确认已失效，请重新验证旧邮箱');
        }
    }

    /**
     * 清除改绑确认令牌
     *
     * @param int $userId
     * @return void
     */
    protected function clearEmailChangeToken(int $userId): void
    {
        $key = RedisKeyManager::getEmailChangeTokenKey($userId);
        $this->redis->executeCommand('DEL', [$key]);
    }
}
