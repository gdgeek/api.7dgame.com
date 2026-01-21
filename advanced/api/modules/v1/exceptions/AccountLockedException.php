<?php

namespace api\modules\v1\exceptions;

use yii\web\TooManyRequestsHttpException;

/**
 * 账户锁定异常
 * 
 * 当账户因多次验证失败而被锁定时抛出此异常
 * HTTP 状态码: 429 Too Many Requests
 * 
 * @author Kiro AI
 * @since 1.0
 */
class AccountLockedException extends TooManyRequestsHttpException
{
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $retryAfter 重试等待时间（秒）
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '验证失败次数过多，账户已被锁定', $retryAfter = 900, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, $retryAfter);
    }
}
