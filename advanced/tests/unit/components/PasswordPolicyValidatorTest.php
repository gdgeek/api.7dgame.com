<?php

namespace tests\unit\components;

use common\components\security\PasswordPolicyValidator;
use PHPUnit\Framework\TestCase;

/**
 * PasswordPolicyValidator 单元测试
 */
class PasswordPolicyValidatorTest extends TestCase
{
    private PasswordPolicyValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new PasswordPolicyValidator();
    }

    public function testValidPasswordPasses(): void
    {
        $result = $this->validator->validate('StrongPass1!xy');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testTooShortPasswordFails(): void
    {
        $result = $this->validator->validate('Short1!a');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testExactMinLengthPasses(): void
    {
        // 12 chars: Abcdefgh1!23
        $result = $this->validator->validate('Abcdefgh1!23');
        $this->assertTrue($result['valid']);
    }

    public function testMissingUppercaseFails(): void
    {
        $result = $this->validator->validate('alllowercase1!');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '大写字母'));
    }

    public function testMissingLowercaseFails(): void
    {
        $result = $this->validator->validate('ALLUPPERCASE1!');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '小写字母'));
    }

    public function testMissingDigitFails(): void
    {
        $result = $this->validator->validate('NoDigitsHere!!');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '数字'));
    }

    public function testMissingSpecialCharFails(): void
    {
        $result = $this->validator->validate('NoSpecial12345');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '特殊字符'));
    }

    public function testWeakPasswordFails(): void
    {
        $result = $this->validator->validate('Password123!');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '常见'));
    }

    public function testWeakPasswordCaseInsensitive(): void
    {
        $result = $this->validator->validate('password123!');
        $this->assertFalse($result['valid']);
    }

    public function testMultipleErrorsReturned(): void
    {
        // Short, no uppercase, no digit, no special
        $result = $this->validator->validate('abc');
        $this->assertFalse($result['valid']);
        $this->assertGreaterThan(1, count($result['errors']));
    }

    public function testGetPolicyDescription(): void
    {
        $desc = $this->validator->getPolicyDescription();
        $this->assertStringContainsString('12', $desc);
    }

    public function testIsWeakPasswordMethod(): void
    {
        $this->assertTrue($this->validator->isWeakPassword('Admin123456!'));
        $this->assertFalse($this->validator->isWeakPassword('Xk9#mP2$vL7@nQ'));
    }

    private function hasErrorContaining(array $errors, string $keyword): bool
    {
        foreach ($errors as $error) {
            if (mb_strpos($error, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}
