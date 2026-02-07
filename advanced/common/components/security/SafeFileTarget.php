<?php

namespace common\components\security;

use yii\log\FileTarget;

/**
 * SafeFileTarget - 自动过滤敏感信息的日志文件目标
 *
 * 继承 Yii2 的 FileTarget，在写入日志前自动调用 LogFilter
 * 过滤密码、令牌、API 密钥等敏感信息。
 *
 * 配置示例:
 * ```php
 * 'log' => [
 *     'targets' => [
 *         [
 *             'class' => 'common\components\security\SafeFileTarget',
 *             'levels' => ['error', 'warning', 'info'],
 *         ],
 *     ],
 * ],
 * ```
 */
class SafeFileTarget extends FileTarget
{
    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        $formatted = parent::formatMessage($message);
        return LogFilter::filter($formatted);
    }
}
