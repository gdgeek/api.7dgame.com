<?php

namespace api\modules\v1;
class RefreshToken extends \yii\redis\ActiveRecord
{
    private const DEFAULT_EXPIRY_SECONDS = 604800;
    
    public function attributes()
    {
        return ['id', 'user_id', 'key', 'session_id', 'user_agent', 'ip', 'created_at', 'expires_at', 'revoked_at'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->expires_at)) {
            $this->expires_at = time() + self::expirySeconds();
        }
        if (empty($this->created_at)) {
            $this->created_at = time();
        }

        return true;
    }

    public static function expirySeconds(): int
    {
        $seconds = (int)(getenv('JWT_REFRESH_TOKEN_EXPIRY') ?: self::DEFAULT_EXPIRY_SECONDS);
        return $seconds > 0 ? $seconds : self::DEFAULT_EXPIRY_SECONDS;
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function isExpired(): bool
    {
        return !empty($this->expires_at) && (int)$this->expires_at <= time();
    }

    public function isRevoked(): bool
    {
        return !empty($this->revoked_at);
    }

}
