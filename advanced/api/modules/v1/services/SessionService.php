<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use api\modules\v1\RefreshToken;
use Yii;
use yii\base\Component;
use yii\web\Request;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class SessionService extends Component
{
    public function issueToken(User $user, array $context = []): array
    {
        $refreshToken = Yii::$app->security->generateRandomString(64);
        $sessionId = $context['session_id'] ?? Yii::$app->security->generateRandomString(32);
        $now = new \DateTimeImmutable('now', new \DateTimeZone(Yii::$app->timeZone));
        $expires = $now->modify('+3 hour');

        $token = new RefreshToken();
        $token->user_id = $user->id;
        $token->key = RefreshToken::hashToken($refreshToken);
        $token->session_id = $sessionId;
        $token->user_agent = $context['user_agent'] ?? null;
        $token->ip = $context['ip'] ?? null;
        $token->created_at = time();
        $token->expires_at = time() + RefreshToken::expirySeconds();

        if (!$token->save()) {
            throw new ServerErrorHttpException('Failed to create refresh token.');
        }

        return [
            'accessToken' => $user->generateAccessToken($now, $expires, (string)$sessionId),
            'expires' => $expires->format('Y-m-d H:i:s'),
            'refreshToken' => $refreshToken,
        ];
    }

    public function consumeRefreshToken(string $refreshToken): User
    {
        $token = $this->findRefreshTokenRecord($refreshToken);
        if (!$token) {
            throw new UnauthorizedHttpException('Refresh token is invalid.');
        }
        if ($token->isRevoked()) {
            $token->delete();
            throw new UnauthorizedHttpException('Refresh token is invalid.');
        }
        if ($token->isExpired()) {
            $token->delete();
            throw new UnauthorizedHttpException('Refresh token is expired.');
        }

        $user = User::findIdentity($token->user_id);
        if (!$user) {
            $token->delete();
            throw new UnauthorizedHttpException('User is not found.');
        }

        $token->delete();
        return $user;
    }

    public function revokeRefreshToken(?string $refreshToken): bool
    {
        if ($refreshToken === null || $refreshToken === '') {
            return true;
        }

        $token = $this->findRefreshTokenRecord($refreshToken);
        if (!$token) {
            return true;
        }
        if ($token->isRevoked()) {
            return true;
        }

        $token->revoked_at = time();
        return (bool)$token->save(false);
    }

    public function revokeUserSessions(int $userId): int
    {
        return (int)RefreshToken::deleteAll(['user_id' => $userId]);
    }

    public function findRefreshTokenRecord(string $refreshToken): ?RefreshToken
    {
        if ($refreshToken === '') {
            return null;
        }

        $tokenHash = RefreshToken::hashToken($refreshToken);
        $token = RefreshToken::find()->where(['key' => $tokenHash])->one();

        // Backward-compatible lookup for existing plaintext refresh token records.
        if (!$token) {
            $token = RefreshToken::find()->where(['key' => $refreshToken])->one();
        }

        return $token instanceof RefreshToken ? $token : null;
    }

    public function contextFromRequest(?Request $request = null): array
    {
        $request = $request ?? Yii::$app->request;

        return [
            'ip' => $request->userIP,
            'user_agent' => $request->userAgent,
        ];
    }
}
