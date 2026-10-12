<?php

namespace common\components;

use api\modules\v1\services\IndependentIdentity;
use common\components\security\ServiceUnavailableHttpException;

/** Defence for old AR entry points, including bulk operations bypassing hooks. */
trait IdentityOwnedRecordGuard
{
    public function beforeSave($insert)
    {
        self::assertIdentityWriter();
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        self::assertIdentityWriter();
        return parent::beforeDelete();
    }

    public static function updateAll($attributes, $condition = '', $params = [])
    {
        self::assertIdentityWriter();
        return parent::updateAll($attributes, $condition, $params);
    }

    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        self::assertIdentityWriter();
        return parent::updateAllCounters($counters, $condition, $params);
    }

    public static function deleteAll($condition = null, $params = [])
    {
        self::assertIdentityWriter();
        return parent::deleteAll($condition, $params);
    }

    private static function assertIdentityWriter(): void
    {
        if (IndependentIdentity::enabled()) {
            throw new ServiceUnavailableHttpException('This account record is owned by Identity; local writes are disabled.');
        }
    }
}
