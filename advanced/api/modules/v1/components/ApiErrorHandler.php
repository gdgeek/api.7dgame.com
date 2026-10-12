<?php

namespace api\modules\v1\components;

use api\modules\v1\exceptions\PluginAccessConfigUnavailableException;
use yii\web\ErrorHandler;

/** Preserves Yii error responses, adding only explicitly defined protocol codes. */
class ApiErrorHandler extends ErrorHandler
{
    protected function convertExceptionToArray($exception)
    {
        $result = parent::convertExceptionToArray($exception);
        if ($exception instanceof PluginAccessConfigUnavailableException) {
            $result['error_code'] = PluginAccessConfigUnavailableException::ERROR_CODE;
        }
        return $result;
    }
}
