<?php

namespace api\modules\v1\exceptions;

use yii\web\UnauthorizedHttpException;

/** A device authorization failure must never fall back to another credential type. */
class DeviceSnAuthenticationException extends UnauthorizedHttpException
{
    public function __construct(string $message = 'Device SN authorization is invalid.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
