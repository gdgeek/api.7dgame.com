<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use yii\base\Component;
use yii\web\BadRequestHttpException;

class IdentityService extends Component
{
    private ?SessionService $sessionService = null;
    private ?LoginAuditReporter $loginAuditReporter = null;

    public function login($username, $password, array $context = []): array
    {
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        $user = User::findByUsername($username);
        if (!$user) {
            throw new BadRequestHttpException("no user");
        }
        if (!$user->validatePassword($password)) {
            throw new BadRequestHttpException("wrong password");
        }

        $context['session_id'] = $context['session_id'] ?? \Yii::$app->security->generateRandomString(32);
        $token = $this->sessionService()->issueToken($user, $context);
        $this->loginAuditReporter()->reportSuccessfulLogin($user, (string)$username, $context);

        return $token;
    }

    public function refresh($refreshToken, array $context = []): array
    {
        if (!is_string($refreshToken) || $refreshToken === '') {
            throw new BadRequestHttpException("refreshToken is required");
        }

        $user = $this->sessionService()->consumeRefreshToken($refreshToken);
        if (!$user instanceof User) {
            throw new BadRequestHttpException("no user");
        }

        if ($user->validate()) {
            $user->save();
        } else {
            throw new BadRequestHttpException("save error");
        }

        return $this->sessionService()->issueToken($user, $context);
    }

    public function logout(?string $refreshToken): bool
    {
        return $this->sessionService()->revokeRefreshToken($refreshToken);
    }

    public function sessionService(): SessionService
    {
        if ($this->sessionService === null) {
            $this->sessionService = new SessionService();
        }

        return $this->sessionService;
    }

    public function loginAuditReporter(): LoginAuditReporter
    {
        if ($this->loginAuditReporter === null) {
            $this->loginAuditReporter = new LoginAuditReporter();
        }

        return $this->loginAuditReporter;
    }
}
