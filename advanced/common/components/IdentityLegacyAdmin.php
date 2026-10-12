<?php

namespace common\components;

use api\modules\v1\services\IdentityProviderClient;
use Yii;

/** Internal service credentials never substitute for an admin actor token. */
class IdentityLegacyAdmin
{
    public static function call(string $operation, array $payload = [], bool $publicTrial = false): array
    {
        $access = null;
        if (!$publicTrial) {
            if (Yii::$app->user instanceof IdentityBackendUser) {
                $access = Yii::$app->user->identityAccessToken();
            } elseif (preg_match('/^Bearer\s+(\S+)$/i', (string)Yii::$app->request->headers->get('Authorization'), $matches) === 1) {
                $access = $matches[1];
            }
            if ($access === null) {
                throw new \yii\web\UnauthorizedHttpException('An Identity administrator session is required.');
            }
        }
        $data = Yii::createObject(IdentityProviderClient::class)->legacyAdmin($operation, $payload, $access);
        return is_array($data['data'] ?? null) ? $data['data'] : $data;
    }
}
