<?php

namespace api\modules\v1\filters;

use api\modules\v1\services\IndependentIdentity;
use yii\web\UnauthorizedHttpException;

/** Uses Identity's verifier for both new and explicitly retained old JWTs. */
class IdentityJwtHttpBearerAuth extends \bizley\jwt\JwtHttpBearerAuth
{
    public function authenticate($user, $request, $response): ?\yii\web\IdentityInterface
    {
        if (!IndependentIdentity::enabled()) {
            return parent::authenticate($user, $request, $response);
        }

        $authorization = (string)$request->headers->get('Authorization', '');
        if (strlen($authorization) > 16384 || preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        try {
            $identity = IndependentIdentity::authority()->verifyAccessToken($matches[1]);
        } catch (UnauthorizedHttpException $exception) {
            return null;
        }
        // Transport errors deliberately propagate as 503. Never authenticate
        // from a stale local account or token-embedded role list.
        $user->switchIdentity($identity);
        return $identity;
    }
}
