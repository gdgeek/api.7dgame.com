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
        $result = $this->validator->validate('Sh1!abc');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testExactMinLengthPasses(): void
    {
        // 8 chars, 3 categories
        $result = $this->validator->validate('Abcdef12');
        $this->assertTrue($result['valid']);
    }

    public function testOneMissingCategoryStillPasses(): void
    {
        $result = $this->validator->validate('NoSpecial12345');
        $this->assertTrue($result['valid']);
    }

    public function testOnlyTwoCategoriesFails(): void
    {
        $result = $this->validator->validate('lowercase123');
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '至少包含 3 类'));
    }

    public function testCountsCharacterCategories(): void
    {
        $this->assertSame(4, $this->validator->countCharacterCategories('Abc123!@'));
        $this->assertSame(3, $this->validator->countCharacterCategories('Abc12345'));
        $this->assertSame(2, $this->validator->countCharacterCategories('abc12345'));
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
        $this->assertStringContainsString('8', $desc);
        $this->assertStringContainsString('64', $desc);
        $this->assertStringContainsString('3', $desc);
    }

    public function testIsWeakPasswordMethod(): void
    {
        $this->assertTrue($this->validator->isWeakPassword('Admin123456!'));
        $this->assertFalse($this->validator->isWeakPassword('Xk9#mP2$vL7@nQ'));
    }

    public function testPasswordContainingUsernameFails(): void
    {
        $result = $this->validator->validate('Dirui2026!', ['username' => 'dirui']);
        $this->assertFalse($result['valid']);
        $this->assertTrue($this->hasErrorContaining($result['errors'], '用户名'));
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
