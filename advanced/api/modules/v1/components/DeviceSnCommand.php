<?php

namespace api\modules\v1\components;

/** Do not expose credential ciphertext or query parameters in SQL exception logs. */
class DeviceSnCommand extends \yii\db\Command
{
    public function execute()
    {
        try {
            return parent::execute();
        } catch (\yii\db\Exception $exception) {
            throw $this->sanitized($exception);
        }
    }

    protected function queryInternal($method, $fetchMode = null)
    {
        try {
            return parent::queryInternal($method, $fetchMode);
        } catch (\yii\db\Exception $exception) {
            throw $this->sanitized($exception);
        }
    }

    private function sanitized(\yii\db\Exception $exception): \yii\db\Exception
    {
        $class = $exception instanceof \yii\db\IntegrityException
            ? \yii\db\IntegrityException::class : \yii\db\Exception::class;
        // Keep only codes needed for conflict/retry handling, never raw SQL, values
        // or the original exception chain (Yii appends getRawSql() to its message).
        return new $class('SN database operation failed.', [
            $exception->errorInfo[0] ?? null,
            $exception->errorInfo[1] ?? null,
            'SN database operation failed.',
        ], (int)$exception->getCode());
    }
}
