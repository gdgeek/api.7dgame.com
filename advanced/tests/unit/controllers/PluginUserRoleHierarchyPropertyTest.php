<?php

namespace tests\unit\controllers;

use api\modules\v1\components\PluginUserRolePolicy;
use PHPUnit\Framework\TestCase;

/**
 * Feature: system-admin-db-decoupling, Property 10: 角色层级约束
 *
 * 对所有合法的操作者角色集合、目标角色集合和待分配角色组合，
 * 角色变更约束都应满足 design.md 中定义的不变量。
 */
class PluginUserRoleHierarchyPropertyTest extends TestCase
{
    private const VALID_ROLES = ['root', 'admin', 'manager', 'user'];

    private const ROLE_LEVELS = [
        'root' => 4,
        'admin' => 3,
        'manager' => 2,
        'user' => 1,
    ];

    public function testProperty10RoleHierarchyConstraints(): void
    {
        foreach ($this->roleSubsets() as $operatorRoles) {
            foreach ($this->roleSubsets() as $targetRoles) {
                foreach (self::VALID_ROLES as $newRole) {
                    $expected = $this->expectedError($operatorRoles, $targetRoles, $newRole);
                    $actual = PluginUserRolePolicy::validateRoleChange(
                        $operatorRoles,
                        $targetRoles,
                        $newRole
                    );

                    $this->assertSame(
                        $expected,
                        $actual,
                        sprintf(
                            'operator=%s target=%s newRole=%s',
                            json_encode($operatorRoles, JSON_UNESCAPED_UNICODE),
                            json_encode($targetRoles, JSON_UNESCAPED_UNICODE),
                            $newRole
                        )
                    );
                }
            }
        }
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function roleSubsets(): array
    {
        $roles = self::VALID_ROLES;
        $result = [];

        for ($mask = 0; $mask < (1 << count($roles)); $mask++) {
            $subset = [];
            foreach ($roles as $index => $role) {
                if (($mask & (1 << $index)) !== 0) {
                    $subset[] = $role;
                }
            }
            $result[] = $subset;
        }

        return $result;
    }

    /**
     * @return array<string, int|string>|null
     */
    private function expectedError(array $operatorRoles, array $targetRoles, string $newRole): ?array
    {
        if ($newRole === 'root') {
            return ['code' => 4001, 'message' => '不允许通过此接口分配 root 角色'];
        }

        if (in_array('root', $targetRoles, true)) {
            return ['code' => 2006, 'message' => 'root 用户角色不可修改'];
        }

        $operatorLevel = $this->roleLevel($operatorRoles);
        $targetLevel = $this->roleLevel($targetRoles);
        $newLevel = self::ROLE_LEVELS[$newRole];

        if ($targetLevel > $operatorLevel) {
            return ['code' => 2004, 'message' => '不能修改比自己角色级别高的用户'];
        }

        if ($newLevel > $operatorLevel) {
            return ['code' => 2005, 'message' => '不能赋予高于自己角色级别的角色'];
        }

        return null;
    }

    private function roleLevel(array $roles): int
    {
        $maxLevel = 0;
        foreach ($roles as $role) {
            $maxLevel = max($maxLevel, self::ROLE_LEVELS[$role] ?? 0);
        }

        return $maxLevel;
    }
}
