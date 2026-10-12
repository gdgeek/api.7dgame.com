<?php

namespace common\components;

use api\modules\v1\services\IdentityProviderClient;
use api\modules\v1\services\IndependentIdentity;
use Yii;
use yii\web\UnauthorizedHttpException;

/** PHP stores UI session tokens; Identity checks every authenticated request. */
class IdentityBackendUser extends \yii\web\User
{
    public ?IdentityProviderClient $identityClient = null;
    private const TOKENS = '__identity_backend_tokens';

    public function identityAccessToken(): ?string
    {
        if (!IndependentIdentity::enabled() || $this->getIdentity() === null) {
            return null;
        }
        $tokens = Yii::$app->session->get(self::TOKENS);
        return is_array($tokens) ? $this->access($tokens) : null;
    }

    public function loginWithIdentityTokens(array $tokens, int $duration = 0): bool
    {
        $identity = IndependentIdentity::authority()->verifyAccessToken($this->access($tokens));
        // The old remember-me auth_key cookie must never become a new authority.
        // Tokens remain only in the server-side PHP session.
        $autoLogin = $this->enableAutoLogin;
        $this->enableAutoLogin = false;
        try {
            $loggedIn = parent::login($identity, 0);
        } finally {
            $this->enableAutoLogin = $autoLogin;
        }
        if ($loggedIn) {
            Yii::$app->session->set(self::TOKENS, $tokens);
            if ($duration > 0) {
                $session = Yii::$app->session;
                $session->setTimeout(max($session->getTimeout(), $duration));
                $rawParams = $session->getCookieParams();
                $cookieParams = ['path' => $rawParams['path'], 'domain' => $rawParams['domain'],
                    'secure' => $rawParams['secure'], 'httpOnly' => $rawParams['httponly'],
                    'sameSite' => $rawParams['samesite'] ?? \yii\web\Cookie::SAME_SITE_LAX];
                $session->set('__identity_backend_absolute_expires', time() + $duration);
                Yii::$app->response->cookies->add(new \yii\web\Cookie(array_merge($cookieParams, [
                    'name' => $session->getName(), 'value' => $session->getId(), 'expire' => time() + $duration,
                ])));
            }
        }
        return $loggedIn;
    }

    protected function renewAuthStatus()
    {
        if (!IndependentIdentity::enabled()) {
            parent::renewAuthStatus();
            return;
        }
        $session = Yii::$app->session;
        $tokens = $session->get(self::TOKENS);
        try {
            if (!is_array($tokens)) {
                $id = $session->get($this->idParam);
                $authKey = $session->get($this->authKeyParam);
                if ($id === null || !is_string($authKey) || $authKey === '') {
                    if (!$this->enableAutoLogin) {
                        $this->setIdentity(null);
                        return;
                    }
                    // Yii Request has already validated the cookie signature.
                    if (!Yii::$app->request->enableCookieValidation) {
                        throw new \common\components\security\ServiceUnavailableHttpException('Signed PHP login cookies are required.');
                    }
                    $value = Yii::$app->request->cookies->getValue($this->identityCookie['name']);
                    $cookie = is_string($value) ? json_decode($value, true) : null;
                    if (!is_array($cookie) || count($cookie) !== 3) {
                        $this->setIdentity(null);
                        return;
                    }
                    [$id, $authKey, $duration] = $cookie;
                    if (!is_int($duration) || $duration <= 0) {
                        throw new UnauthorizedHttpException('Legacy PHP login cookie is invalid.');
                    }
                }
                $id = filter_var($id, FILTER_VALIDATE_INT);
                if ($id === false || $id <= 0 || !is_string($authKey) || $authKey === '') {
                    throw new UnauthorizedHttpException('Legacy PHP login session is invalid.');
                }
                $tokens = $this->provider()->legacyPhpSession($id, $authKey);
                $identity = IndependentIdentity::authority()->verifyAccessToken($this->access($tokens));
                // Bind a new Identity session once. Do not renew the old cookie.
                $session->regenerateID(true);
                $session->set($this->idParam, $identity->getId());
                $session->remove($this->authKeyParam);
                $session->set(self::TOKENS, $tokens);
                $this->setIdentity($identity);
            } else {
                try {
                    $identity = IndependentIdentity::authority()->verifyAccessToken($this->access($tokens));
                } catch (UnauthorizedHttpException $exception) {
                    $refresh = $tokens['refreshToken'] ?? null;
                    if (!is_string($refresh) || $refresh === '') {
                        throw $exception;
                    }
                    $tokens = $this->provider()->refresh($refresh);
                    $identity = IndependentIdentity::authority()->verifyAccessToken($this->access($tokens));
                    $session->set(self::TOKENS, $tokens);
                }
                $this->setIdentity($identity);
            }
            foreach ([$this->authTimeoutParam, $this->absoluteAuthTimeoutParam, '__identity_backend_absolute_expires'] as $key) {
                $expires = $session->get($key);
                if ($expires !== null && $expires < time()) {
                    $this->logout(false);
                    return;
                }
            }
            if ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        } catch (UnauthorizedHttpException $exception) {
            $this->setIdentity(null);
            $session->remove(self::TOKENS);
            $session->remove($this->idParam);
            $session->remove($this->authKeyParam);
            $this->removeIdentityCookie();
        }
        // Transport failures propagate; they must not log the caller in locally.
    }

    public function logout($destroySession = true)
    {
        if (IndependentIdentity::enabled()) {
            $tokens = Yii::$app->session->get(self::TOKENS);
            if (is_array($tokens) && is_string($tokens['refreshToken'] ?? null)) {
                $this->provider()->logout($tokens['refreshToken']);
            }
            Yii::$app->session->remove(self::TOKENS);
            Yii::$app->session->remove('__identity_backend_absolute_expires');
            if ($this->getIdentity(false) === null) {
                // parent::logout calls getIdentity(true). Never exchange a
                // surviving legacy cookie, or refresh, while logging out.
                $this->switchIdentity(null);
                if ($destroySession && $this->enableSession) Yii::$app->session->destroy();
                return true;
            }
        }
        return parent::logout($destroySession);
    }

    private function access(array $tokens): string
    {
        $access = $tokens['accessToken'] ?? $tokens['token'] ?? null;
        if (!is_string($access) || $access === '') {
            throw new UnauthorizedHttpException('Identity PHP session is invalid.');
        }
        return $access;
    }

    private function provider(): IdentityProviderClient
    {
        return $this->identityClient ??= new IdentityProviderClient();
    }
}
