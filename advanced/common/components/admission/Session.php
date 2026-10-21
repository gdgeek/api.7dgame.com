<?php

declare(strict_types=1);

namespace common\components\admission;

/** Observe every Yii session close, not only the runner's final close. */
final class Session extends \yii\web\Session
{
    public function close()
    {
        HttpAdmission::closePhpSession();
        parent::close(); // Inactive now; retain Yii's own local bookkeeping.
    }
}
