<?php

namespace api\modules\v1\components;

use api\modules\v1\models\User;
use Yii;
use yii\web\ForbiddenHttpException;

final class DeviceSnAuthGuard
{
    public static function assertCredentialManagementAllowed(?User $identity = null): void
    {
        $identity = $identity ?? Yii::$app->user->identity;
        if ($identity instanceof User && DeviceSnAuthContext::normalize($identity->authContext) !== []) {
            throw new ForbiddenHttpException('Device SN sessions cannot create or change account credentials.');
        }
    }
}
