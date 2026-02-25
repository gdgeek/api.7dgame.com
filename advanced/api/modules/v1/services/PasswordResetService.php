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
 */
class PasswordResetService extends Component
{
    /**
     * 验证码长度（兼容旧流程）
     */
    const CODE_LENGTH = 6;

    /**
     * 验证码过期时间（秒）
     */
    const CODE_TTL = 900; // 15 分钟

    /**
     * 发送重置验证码速率限制（秒）
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
     * @var RateLimiter
     */
    protected $rateLimiter;

    /**
     * @var \yii\redis\Connection
     */
    protected $redis;

    public function init()
    {
        parent::init();
        $this->rateLimiter = new RateLimiter();
        $this->redis = Yii::$app->redis;
    }

    /**
     * 生成并发送密码重置验证码
     */
    public function sendResetCode(string $email, string $locale = 'en-US', array $i18n = []): string
    {
        if (!$this->isEmailVerified($email)) {
            Yii::warning("Password reset requested for unverified email: {$email}", __METHOD__);
            throw new BadRequestHttpException('邮箱未验证，无法重置密码');
        }

        $rateLimitKey = RedisKeyManager::getRateLimitKey($email, 'request_reset');
        if ($this->rateLimiter->tooManyAttempts($rateLimitKey, 1, self::RATE_LIMIT_TTL)) {
            $retryAfter = $this->rateLimiter->availableIn($rateLimitKey);
            throw new TooManyRequestsHttpException("请求过于频繁，请 {$retryAfter} 秒后再试", 0, null, $retryAfter);
        }

        $user = User::findByEmail($email);
        if ($user === null) {
            Yii::error("User not found for email: {$email}", __METHOD__);
            throw new BadRequestHttpException('用户不存在');
        }

        $code = $this->generateResetCode();
        $codeKey = RedisKeyManager::getResetCodeKey($email);
        $codeData = json_encode([
            'code' => $code,
            'email' => $email,
            'user_id' => $user->id,
            'created_at' => time(),
        ]);
        $this->redis->executeCommand('SETEX', [$codeKey, self::CODE_TTL, $codeData]);

        $emailService = new EmailService();
        $emailSent = $emailService->sendPasswordResetCode($email, $code, $locale, $i18n);
        if (!$emailSent) {
            Yii::warning("Failed to send password reset code email to {$email}, but code was stored in Redis", __METHOD__);
        }

        $this->rateLimiter->hit($rateLimitKey, self::RATE_LIMIT_TTL);
        Yii::info("Password reset code sent to {$email}", __METHOD__);

        return $code;
    }

    /**
     * 验证重置验证码（旧流程）
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
     */
    public function resetPassword(string $email, string $code, string $newPassword): bool
    {
        return $this->resetPasswordWithCode($email, $code, $newPassword);
    }

    /**
     * 登录态修改密码（需邮箱已验证）
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

        $this->invalidateUserSessions((int)$user->id);
        Yii::info("Password changed successfully for user ID: {$user->id}", __METHOD__);

        return true;
    }

    protected function resetPasswordWithCode(string $email, string $code, string $newPassword): bool
    {
        $this->verifyResetCode($email, $code);

        $user = User::findByEmail($email);
        if ($user === null) {
            Yii::error("User not found for email: {$email}", __METHOD__);
            throw new BadRequestHttpException('用户不存在');
        }

        $user->setPassword($newPassword);
        if (!$user->save(false)) {
            Yii::error("Failed to save new password for user ID: {$user->id}", __METHOD__);
            return false;
        }

        $this->clearResetCodeData($email);
        $this->invalidateUserSessions((int)$user->id);
        Yii::info("Password reset successful for user ID: {$user->id}", __METHOD__);

        return true;
    }

    protected function generateResetCode(): string
    {
        $randomBytes = Yii::$app->security->generateRandomString(self::CODE_LENGTH);
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= ord($randomBytes[$i]) % 10;
        }
        return $code;
    }

    protected function isEmailVerified(string $email): bool
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            return false;
        }

        return $user->isEmailVerified();
    }

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

    protected function isCodeLocked(string $email): bool
    {
        $attemptsKey = RedisKeyManager::getResetAttemptsKey($email);
        return $this->rateLimiter->tooManyAttempts($attemptsKey, self::MAX_ATTEMPTS, self::LOCK_TTL);
    }

    protected function incrementCodeAttempts(string $email): int
    {
        $attemptsKey = RedisKeyManager::getResetAttemptsKey($email);
        return $this->rateLimiter->hit($attemptsKey, self::LOCK_TTL);
    }

    protected function clearResetCodeData(string $email): void
    {
        $keys = [
            RedisKeyManager::getResetCodeKey($email),
            RedisKeyManager::getResetAttemptsKey($email),
        ];
        $this->rateLimiter->clearMany($keys);
    }
}
