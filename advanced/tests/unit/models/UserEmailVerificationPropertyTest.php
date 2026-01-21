<?php

namespace tests\unit\models;

use PHPUnit\Framework\TestCase;
use api\modules\v1\models\User;
use Yii;

/**
 * User 邮箱验证状态属性测试
 * 
 * 使用属性测试验证 User 模型邮箱验证状态判断的通用正确性属性
 * 
 * @group Feature: email-verification-and-password-reset
 */
class UserEmailVerificationPropertyTest extends TestCase
{
    /**
     * 检查数据库是否可用
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 检查数据库是否可用
        try {
            if (!Yii::$app->db) {
                $this->markTestSkipped('Database component is not configured');
            }
            Yii::$app->db->open();
        } catch (\Exception $e) {
            $this->markTestSkipped('Database is not available: ' . $e->getMessage());
        }
    }
    
    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 清理测试用户
        if (Yii::$app->db) {
            try {
                User::deleteAll(['like', 'username', 'proptest_']);
            } catch (\Exception $e) {
                // 忽略数据库错误
            }
        }
    }
    
    /**
     * Property 17: 邮箱验证状态判断
     * 
     * 对于任何用户，当 email_verified_at 字段不为 NULL 时，isEmailVerified() 方法必须返回 true；
     * 当为 NULL 时必须返回 false。
     * 
     * **Validates: Requirements 9.2, 9.3, 9.4**
     * 
     * @group Property 17: 邮箱验证状态判断
     */
    public function testProperty17EmailVerificationStatusDetermination()
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // 创建新用户实例（不保存到数据库）
            $user = new User();
            $user->username = 'proptest_user_' . $i . '@example.com';
            $user->email = $user->username;
            // 设置必需的属性以避免触发数据库验证
            $user->auth_key = 'test_key_' . $i;
            $user->password_hash = 'test_hash_' . $i;
            
            // 测试场景 1: email_verified_at 为 NULL
            $user->email_verified_at = null;
            
            $this->assertFalse(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return false when email_verified_at is NULL"
            );
            
            // 测试场景 2: email_verified_at 为 0（边界情况）
            $user->email_verified_at = 0;
            
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true when email_verified_at is 0 (not NULL)"
            );
            
            // 测试场景 3: email_verified_at 为当前时间戳
            $user->email_verified_at = time();
            
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true when email_verified_at is current timestamp"
            );
            
            // 测试场景 4: email_verified_at 为过去的时间戳
            $pastTimestamp = time() - rand(1, 86400 * 365); // 随机过去 1 年内的时间
            $user->email_verified_at = $pastTimestamp;
            
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true when email_verified_at is {$pastTimestamp}"
            );
            
            // 测试场景 5: email_verified_at 为未来的时间戳
            $futureTimestamp = time() + rand(1, 86400 * 30); // 随机未来 30 天内的时间
            $user->email_verified_at = $futureTimestamp;
            
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true when email_verified_at is {$futureTimestamp}"
            );
            
            // 测试场景 6: 重新设置为 NULL
            $user->email_verified_at = null;
            
            $this->assertFalse(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return false when email_verified_at is reset to NULL"
            );
        }
    }
    
    /**
     * Property 17 扩展测试: 验证状态在数据库持久化后的一致性
     * 
     * 验证 email_verified_at 字段在保存到数据库后，isEmailVerified() 方法仍然返回正确的结果。
     * 
     * **Validates: Requirements 9.2, 9.3, 9.4**
     * 
     * @group Property 17: 邮箱验证状态判断
     */
    public function testProperty17EmailVerificationStatusWithDatabasePersistence()
    {
        $iterations = 20; // 减少迭代次数以避免数据库负载过高
        
        for ($i = 0; $i < $iterations; $i++) {
            // 创建并保存用户（未验证状态）
            $user = new User();
            $user->username = 'proptest_db_' . $i . '_' . time() . '@example.com';
            $user->email = $user->username;
            $user->setPassword('Test123!@#');
            $user->generateAuthKey();
            $user->email_verified_at = null;
            
            $saved = $user->save();
            $this->assertTrue($saved, "Iteration {$i}: User should be saved successfully");
            
            // 验证未验证状态
            $this->assertFalse(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return false after saving with NULL email_verified_at"
            );
            
            // 从数据库重新加载
            $user->refresh();
            $this->assertFalse(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return false after refresh with NULL email_verified_at"
            );
            
            // 标记为已验证
            $timestamp = time();
            $user->email_verified_at = $timestamp;
            $user->save(false, ['email_verified_at']);
            
            // 验证已验证状态
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true after setting email_verified_at to {$timestamp}"
            );
            
            // 从数据库重新加载
            $user->refresh();
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true after refresh with email_verified_at = {$timestamp}"
            );
            $this->assertEquals(
                $timestamp,
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at should be {$timestamp} after refresh"
            );
            
            // 清理
            $user->delete();
        }
    }
    
    /**
     * Property 17 扩展测试: 验证 markEmailAsVerified() 方法的正确性
     * 
     * 验证 markEmailAsVerified() 方法正确设置 email_verified_at 字段，
     * 并且 isEmailVerified() 方法返回 true。
     * 
     * **Validates: Requirements 9.2, 9.3, 9.4**
     * 
     * @group Property 17: 邮箱验证状态判断
     */
    public function testProperty17MarkEmailAsVerifiedConsistency()
    {
        $iterations = 20;
        
        for ($i = 0; $i < $iterations; $i++) {
            // 创建并保存用户
            $user = new User();
            $user->username = 'proptest_mark_' . $i . '_' . time() . '@example.com';
            $user->email = $user->username;
            $user->setPassword('Test123!@#');
            $user->generateAuthKey();
            $user->email_verified_at = null;
            
            $saved = $user->save();
            $this->assertTrue($saved, "Iteration {$i}: User should be saved successfully");
            
            // 验证初始状态
            $this->assertFalse(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return false before marking as verified"
            );
            $this->assertNull(
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at must be NULL before marking as verified"
            );
            
            // 标记为已验证
            $beforeTime = time();
            $result = $user->markEmailAsVerified();
            $afterTime = time();
            
            // 验证返回值
            $this->assertTrue(
                $result,
                "Iteration {$i}: markEmailAsVerified() must return true"
            );
            
            // 验证状态已更改
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true after marking as verified"
            );
            
            // 验证时间戳在合理范围内
            $this->assertNotNull(
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at must not be NULL after marking as verified"
            );
            $this->assertGreaterThanOrEqual(
                $beforeTime,
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at must be >= {$beforeTime}"
            );
            $this->assertLessThanOrEqual(
                $afterTime,
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at must be <= {$afterTime}"
            );
            
            // 从数据库重新加载验证持久化
            $user->refresh();
            $this->assertTrue(
                $user->isEmailVerified(),
                "Iteration {$i}: isEmailVerified() must return true after refresh"
            );
            $this->assertNotNull(
                $user->email_verified_at,
                "Iteration {$i}: email_verified_at must not be NULL after refresh"
            );
            
            // 清理
            $user->delete();
        }
    }
}
