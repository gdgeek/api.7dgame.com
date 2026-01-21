<?php

namespace tests\unit\models;

use api\modules\v1\models\User;
use PHPUnit\Framework\TestCase;

/**
 * 测试 User 模型的方法签名（不需要数据库连接）
 */
class UserMethodsTest extends TestCase
{
    /**
     * 测试 findByEmail() 静态方法存在
     */
    public function testFindByEmailMethodExists()
    {
        $this->assertTrue(
            method_exists(User::class, 'findByEmail'),
            'User 类应该有 findByEmail 静态方法'
        );
        
        // 验证方法签名
        $reflection = new \ReflectionMethod(User::class, 'findByEmail');
        $this->assertTrue($reflection->isStatic(), 'findByEmail 应该是静态方法');
        $this->assertTrue($reflection->isPublic(), 'findByEmail 应该是公共方法');
        
        // 验证参数
        $parameters = $reflection->getParameters();
        $this->assertCount(1, $parameters, 'findByEmail 应该接受 1 个参数');
        $this->assertEquals('email', $parameters[0]->getName(), '参数名应该是 email');
    }

    /**
     * 测试 isEmailVerified() 方法存在
     */
    public function testIsEmailVerifiedMethodExists()
    {
        $this->assertTrue(
            method_exists(User::class, 'isEmailVerified'),
            'User 类应该有 isEmailVerified 方法'
        );
        
        $reflection = new \ReflectionMethod(User::class, 'isEmailVerified');
        $this->assertTrue($reflection->isPublic(), 'isEmailVerified 应该是公共方法');
        $this->assertFalse($reflection->isStatic(), 'isEmailVerified 不应该是静态方法');
    }

    /**
     * 测试 markEmailAsVerified() 方法存在
     */
    public function testMarkEmailAsVerifiedMethodExists()
    {
        $this->assertTrue(
            method_exists(User::class, 'markEmailAsVerified'),
            'User 类应该有 markEmailAsVerified 方法'
        );
        
        $reflection = new \ReflectionMethod(User::class, 'markEmailAsVerified');
        $this->assertTrue($reflection->isPublic(), 'markEmailAsVerified 应该是公共方法');
        $this->assertFalse($reflection->isStatic(), 'markEmailAsVerified 不应该是静态方法');
    }

    /**
     * 测试 User 类实现了 IdentityInterface
     */
    public function testUserImplementsIdentityInterface()
    {
        $reflection = new \ReflectionClass(User::class);
        $this->assertTrue(
            $reflection->implementsInterface(\yii\web\IdentityInterface::class),
            'User 应该实现 IdentityInterface'
        );
    }

    /**
     * 测试 User 类继承自 ActiveRecord
     */
    public function testUserExtendsActiveRecord()
    {
        $reflection = new \ReflectionClass(User::class);
        $this->assertTrue(
            $reflection->isSubclassOf(\yii\db\ActiveRecord::class),
            'User 应该继承自 ActiveRecord'
        );
    }

    /**
     * 测试必要的静态方法存在
     */
    public function testRequiredStaticMethodsExist()
    {
        $methods = ['findByUsername', 'findByEmail', 'create', 'tableName'];
        
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(User::class, $method),
                "User 类应该有 {$method} 方法"
            );
        }
    }

    /**
     * 测试必要的实例方法存在
     */
    public function testRequiredInstanceMethodsExist()
    {
        $methods = [
            'isEmailVerified',
            'markEmailAsVerified',
            'validatePassword',
            'setPassword',
            'generateAuthKey',
            'generateAccessToken'
        ];
        
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(User::class, $method),
                "User 类应该有 {$method} 方法"
            );
        }
    }
}
