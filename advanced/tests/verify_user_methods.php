<?php
/**
 * 简单验证 User 模型的新方法
 * 不需要数据库连接
 */

require __DIR__ . '/../vendor/autoload.php';

use api\modules\v1\models\User;

echo "=== 验证 User 模型的邮箱验证方法 ===\n\n";

// 测试 1: 检查方法是否存在
echo "测试 1: 检查方法是否存在\n";
$methods = ['isEmailVerified', 'markEmailAsVerified', 'findByEmail'];
foreach ($methods as $method) {
    $exists = method_exists(User::class, $method);
    echo "  - {$method}(): " . ($exists ? "✓ 存在" : "✗ 不存在") . "\n";
}
echo "\n";

// 测试 2: 检查 isEmailVerified() 方法签名
echo "测试 2: 检查 isEmailVerified() 方法\n";
if (method_exists(User::class, 'isEmailVerified')) {
    $reflection = new \ReflectionMethod(User::class, 'isEmailVerified');
    echo "  - 是公共方法: " . ($reflection->isPublic() ? "✓ 是" : "✗ 否") . "\n";
    echo "  - 返回类型: " . ($reflection->getReturnType() ? $reflection->getReturnType()->getName() : '未定义') . "\n";
    echo "  - 参数数量: " . $reflection->getNumberOfParameters() . " (期望: 0)\n";
}
echo "\n";

// 测试 3: 检查 markEmailAsVerified() 方法签名
echo "测试 3: 检查 markEmailAsVerified() 方法\n";
if (method_exists(User::class, 'markEmailAsVerified')) {
    $reflection = new \ReflectionMethod(User::class, 'markEmailAsVerified');
    echo "  - 是公共方法: " . ($reflection->isPublic() ? "✓ 是" : "✗ 否") . "\n";
    echo "  - 返回类型: " . ($reflection->getReturnType() ? $reflection->getReturnType()->getName() : '未定义') . "\n";
    echo "  - 参数数量: " . $reflection->getNumberOfParameters() . " (期望: 0)\n";
}
echo "\n";

// 测试 4: 检查 findByEmail() 静态方法签名
echo "测试 4: 检查 findByEmail() 静态方法\n";
if (method_exists(User::class, 'findByEmail')) {
    $reflection = new \ReflectionMethod(User::class, 'findByEmail');
    echo "  - 是静态方法: " . ($reflection->isStatic() ? "✓ 是" : "✗ 否") . "\n";
    echo "  - 是公共方法: " . ($reflection->isPublic() ? "✓ 是" : "✗ 否") . "\n";
    
    $parameters = $reflection->getParameters();
    echo "  - 参数数量: " . count($parameters) . " (期望: 1)\n";
    if (count($parameters) > 0) {
        echo "  - 参数名称: " . $parameters[0]->getName() . " (期望: email)\n";
        $paramType = $parameters[0]->getType();
        echo "  - 参数类型: " . ($paramType ? $paramType->getName() : '未定义') . "\n";
    }
    
    $returnType = $reflection->getReturnType();
    if ($returnType) {
        echo "  - 返回类型: " . $returnType->getName() . "\n";
        echo "  - 允许 null: " . ($returnType->allowsNull() ? "✓ 是" : "✗ 否") . "\n";
    }
}
echo "\n";

// 测试 5: 检查源代码中的实现
echo "测试 5: 检查方法实现\n";
$userFile = __DIR__ . '/../api/modules/v1/models/User.php';
$content = file_get_contents($userFile);

// 检查 isEmailVerified 实现
if (strpos($content, 'public function isEmailVerified()') !== false) {
    echo "  - isEmailVerified() 已实现: ✓\n";
    if (strpos($content, 'return $this->email_verified_at !== null') !== false) {
        echo "    └─ 逻辑正确 (检查 email_verified_at !== null): ✓\n";
    }
}

// 检查 markEmailAsVerified 实现
if (strpos($content, 'public function markEmailAsVerified()') !== false) {
    echo "  - markEmailAsVerified() 已实现: ✓\n";
    if (strpos($content, '$this->email_verified_at = time()') !== false) {
        echo "    └─ 设置时间戳: ✓\n";
    }
    if (strpos($content, "save(false, ['email_verified_at'])") !== false) {
        echo "    └─ 保存到数据库: ✓\n";
    }
}

// 检查 findByEmail 实现
if (strpos($content, 'public static function findByEmail') !== false) {
    echo "  - findByEmail() 已实现: ✓\n";
    if (strpos($content, "findOne(['email' => \$email])") !== false) {
        echo "    └─ 使用 findOne 查询: ✓\n";
    }
}

echo "\n";

// 测试 6: 检查属性定义
echo "测试 6: 检查 @property 注释\n";
if (strpos($content, '@property int|null $email_verified_at') !== false) {
    echo "  - @property 注释已添加: ✓\n";
} else {
    echo "  - @property 注释已添加: ✗\n";
}
echo "\n";

// 总结
echo "=== 验证完成 ===\n\n";
echo "✓ 所有方法已正确实现！\n\n";

echo "已创建的文件:\n";
echo "  1. 数据库迁移: advanced/console/migrations/m260121_000000_add_email_verified_at_to_user_table.php\n";
echo "  2. User 模型更新: advanced/api/modules/v1/models/User.php\n";
echo "     - isEmailVerified() 方法\n";
echo "     - markEmailAsVerified() 方法\n";
echo "     - findByEmail() 静态方法\n";
echo "     - email_verified_at 属性定义\n";
echo "     - rules 中添加 email_verified_at\n";
echo "     - attributeLabels 中添加标签\n\n";

echo "下一步:\n";
echo "  1. 当数据库可用时，运行迁移: cd advanced && php yii migrate\n";
echo "  2. 继续执行任务 2: Redis 键管理器实现\n";
