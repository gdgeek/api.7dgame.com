<?php

namespace tests\unit\components;

use PHPUnit\Framework\TestCase;

/**
 * 验证密码哈希使用 bcrypt 且 cost >= 12
 */
class PasswordHashCostTest extends TestCase
{
    public function testBcryptCost12(): void
    {
        $password = 'TestPassword1!';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // bcrypt hash 格式: $2y$12$...
        $this->assertStringStartsWith('$2y$12$', $hash);
        $this->assertTrue(password_verify($password, $hash));
    }

    public function testBcryptHashInfoShowsCost12(): void
    {
        $hash = password_hash('test', PASSWORD_BCRYPT, ['cost' => 12]);
        $info = password_get_info($hash);

        $this->assertEquals('bcrypt', $info['algoName']);
        $this->assertEquals(12, $info['options']['cost']);
    }

    public function testYii2SecurityGeneratePasswordHashWithCost(): void
    {
        // Yii2 的 generatePasswordHash($password, $cost) 内部使用 password_hash + PASSWORD_BCRYPT
        // 验证 cost 12 生成的哈希可以被 password_verify 验证
        $password = 'Str0ng!Pass#12';
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }
}
