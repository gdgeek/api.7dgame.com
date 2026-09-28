<?php

namespace api\modules\v1\exceptions;

use yii\web\HttpException;

/** A closed plugin-policy dependency failure, distinct from ordinary business 503s. */
final class PluginAccessConfigUnavailableException extends HttpException
{
    public const ERROR_CODE = 'PLUGIN_ACCESS_CONFIG_UNAVAILABLE';

    public function __construct()
    {
        // Never attach upstream exceptions: they may contain deployment details.
        parent::__construct(503, 'Plugin access configuration is unavailable.');
    }
}
