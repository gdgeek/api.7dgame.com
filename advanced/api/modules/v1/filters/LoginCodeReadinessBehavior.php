<?php

namespace api\modules\v1\filters;

use api\modules\v1\services\LoginCodeReadiness;
use api\modules\v1\services\LoginCodeSettings;
use Yii;
use yii\base\ActionFilter;

/**
 * Runs the Redis clock gate before the login-code issuance limiter reserves
 * allowance. It has no effect in database/database mode.
 */
final class LoginCodeReadinessBehavior extends ActionFilter
{
    public function beforeAction($action)
    {
        $settings = LoginCodeSettings::fromApplication();
        $requiresGate = ($action->id === 'user-linked' && $settings->writesRedis())
            || ($action->id === 'user-linked-status' && $settings->readsRedis());

        if ($requiresGate) {
            $this->readiness()->assertReady();
        }

        return parent::beforeAction($action);
    }

    private function readiness(): LoginCodeReadiness
    {
        if (Yii::$app->has('loginCodeReadiness')) {
            $readiness = Yii::$app->get('loginCodeReadiness');
            if ($readiness instanceof LoginCodeReadiness) {
                return $readiness;
            }
        }

        return new LoginCodeReadiness();
    }
}
