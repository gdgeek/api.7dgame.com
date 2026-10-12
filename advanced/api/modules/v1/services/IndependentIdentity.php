<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\InvalidConfigException;

/** One explicit ownership switch; legacy rollout flags cannot weaken it. */
final class IndependentIdentity
{
    public static function enabled(): bool
    {
        $source = getenv('IDENTITY_DATA_SOURCE');
        if ($source === false && isset(Yii::$app->params['identityAuth']['IDENTITY_DATA_SOURCE'])) {
            $source = Yii::$app->params['identityAuth']['IDENTITY_DATA_SOURCE'];
        }
        $source = strtolower(trim((string)($source === false ? 'legacy' : $source)));
        if (!in_array($source, ['legacy', 'identity'], true)) {
            throw new InvalidConfigException('IDENTITY_DATA_SOURCE must be legacy or identity.');
        }

        return $source === 'identity';
    }

    public static function authority(): IdentityAuthorityService
    {
        return Yii::$app->get('identityAuthority');
    }

    /** HTTP cutover requires an explicit readiness decision after integration drills. */
    public static function assertHttpReady(): void
    {
        if (!self::enabled()) {
            return;
        }
        $ready = getenv('IDENTITY_INDEPENDENT_READY');
        if ($ready === false) {
            $ready = Yii::$app->params['identityAuth']['IDENTITY_INDEPENDENT_READY'] ?? false;
        }
        if (!in_array(strtolower(trim((string)$ready)), ['1', 'true'], true)) {
            throw new InvalidConfigException('Independent Identity HTTP cutover is blocked. Complete the compatibility, import, projection, and rollback drills before setting IDENTITY_INDEPENDENT_READY=true.');
        }
    }
}
