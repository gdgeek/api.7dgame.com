<?php

namespace api\modules\v1\exceptions;

use yii\web\TooManyRequestsHttpException;

/**
 * 速率限制异常
 * 
 * 当用户请求过于频繁时抛出此异常
 * HTTP 状态码: 429 Too Many Requests
 * 
 * @author Kiro AI
 * @since 1.0
 */
class RateLimitException extends TooManyRequestsHttpException
{
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $retryAfter 重试等待时间（秒）
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '请求过于频繁，请稍后再试', $retryAfter = 60, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, $retryAfter);
    }
}
