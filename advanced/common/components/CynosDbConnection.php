<?php
namespace common\components;

use yii\db\Connection;
use yii\db\Exception;

class CynosDbConnection extends Connection
{
    // 指定使用自定义的 Command 类
    public $commandClass = 'common\components\CynosDbCommand';
    
    // 配置重试参数
    public $maxRetries = 3;
    public $retryInterval = 1; // 秒

    /**
     * 重写打开连接方法，增加重试
     */
    public function open()
    {
        $this->retry(function () {
            parent::open();
        });
    }

    /**
     * 通用重试逻辑
     */
    public function retry(callable $func)
    {
        $attempts = 0;
        while (true) {
            try {
                return call_user_func($func);
            } catch (Exception $e) {
                if ($this->isRecoverableError($e->getMessage())) {
                    $attempts++;
                    if ($attempts >= $this->maxRetries) {
                        throw $e; // 重试次数耗尽，抛出异常
                    }
                    // 等待数据库唤醒
                    sleep($this->retryInterval);
                    \Yii::warning("CynosDB resuming... Retrying DB operation (Attempt $attempts)", __METHOD__);
                    continue;
                }
                throw $e; // 其他错误直接抛出
            } catch (\Exception $e) {
                 throw $e;
            }
        }
    }

    /**
     * 判断是否为可恢复的冷启动错误
     */
    protected function isRecoverableError($message)
    {
        return strpos($message, 'resuming') !== false || strpos($message, 'CynosDB') !== false;
    }
}
