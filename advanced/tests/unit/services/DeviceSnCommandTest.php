<?php

namespace tests\unit\services;

use api\modules\v1\components\DeviceSnCommand;
use PHPUnit\Framework\TestCase;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\IntegrityException;

final class DeviceSnCommandTest extends TestCase
{
    public function testWriteConflictKeepsClassificationWithoutLeakingCredentialValues(): void
    {
        $db = $this->database();
        try {
            $db->createCommand('CREATE TABLE credential (sn_ciphertext TEXT UNIQUE)')->execute();
            $value = 'sensitive-ciphertext-' . bin2hex(random_bytes(16));
            $db->createCommand()->insert('credential', ['sn_ciphertext' => $value])->execute();
            try {
                $db->createCommand()->insert('credential', ['sn_ciphertext' => $value])->execute();
                self::fail('Expected unique constraint failure.');
            } catch (IntegrityException $exception) {
                self::assertNotEmpty($exception->errorInfo[0]);
                self::assertNotEmpty($exception->errorInfo[1]);
                self::assertStringNotContainsString($value, (string)$exception);
                self::assertStringNotContainsString('INSERT', $exception->getMessage());
                self::assertNull($exception->getPrevious());
            }
        } finally {
            $db->close();
        }
    }

    public function testReadFailureDoesNotExposeQueryOrCredentialDigest(): void
    {
        $db = $this->database();
        try {
            $value = hash('sha256', random_bytes(32));
            try {
                $db->createCommand('SELECT * FROM missing_device_sn WHERE sn_hash = :hash', [':hash' => $value])->queryOne();
                self::fail('Expected missing table failure.');
            } catch (Exception $exception) {
                self::assertStringNotContainsString($value, (string)$exception);
                self::assertStringNotContainsString('SELECT', $exception->getMessage());
                self::assertNull($exception->getPrevious());
            }
        } finally {
            $db->close();
        }
    }

    private function database(): Connection
    {
        return new Connection(['dsn' => 'sqlite::memory:', 'commandClass' => DeviceSnCommand::class,
            'enableLogging' => false, 'enableProfiling' => false]);
    }
}
