<?php

namespace api\modules\v1\exceptions;

use yii\web\BadRequestHttpException;

/**
 * 邮箱未验证异常
 * 
 * 当尝试执行需要已验证邮箱的操作时抛出此异常
 * HTTP 状态码: 400 Bad Request
 * 
 * @author Kiro AI
 * @since 1.0
 */
class EmailNotVerifiedException extends BadRequestHttpException
{
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct($message = '邮箱未验证，无法执行此操作', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
