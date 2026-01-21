<?php

namespace api\modules\v1\exceptions;

use yii\web\BadRequestHttpException;

/**
 * 验证码无效异常
 * 
 * 当验证码不正确、已过期或不存在时抛出此异常
 * HTTP 状态码: 400 Bad Request
 * 
 * @author Kiro AI
 * @since 1.0
 */
class InvalidCodeException extends BadRequestHttpException
{
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '验证码无效或已过期', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
