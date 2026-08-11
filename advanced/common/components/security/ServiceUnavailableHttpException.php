<?php

namespace common\components\security;

use Throwable;
use yii\web\HttpException;

/**
 * Yii2 does not ship a dedicated 503 exception class. Keep the transport
 * contract explicit for infrastructure failures that must fail closed.
 */
final class ServiceUnavailableHttpException extends HttpException
{
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(503, $message, $code, $previous);
    }
}
