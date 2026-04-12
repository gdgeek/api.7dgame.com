<?php

namespace api\modules\v1\components;

final class PluginUserRolePolicy
{
    private const ROLE_LEVELS = [
        'root' => 4,
        'admin' => 3,
        'manager' => 2,
        'user' => 1,
    ];

    private function __construct()
    {
    }

    public static function getRoleLevel(array $roles): int
    {
        $maxLevel = 0;

        foreach ($roles as $role) {
            $maxLevel = max($maxLevel, self::ROLE_LEVELS[$role] ?? 0);
        }

        return $maxLevel;
    }

    public static function validateRoleChange(array $operatorRoles, array $targetRoles, string $newRole): ?array
    {
        if ($newRole === 'root') {
            return ['code' => 4001, 'message' => '不允许通过此接口分配 root 角色'];
        }

        if (in_array('root', $targetRoles, true)) {
            return ['code' => 2006, 'message' => 'root 用户角色不可修改'];
        }

        $operatorLevel = self::getRoleLevel($operatorRoles);
        $targetLevel = self::getRoleLevel($targetRoles);
        $newLevel = self::ROLE_LEVELS[$newRole] ?? 0;

        if ($targetLevel > $operatorLevel) {
            return ['code' => 2004, 'message' => '不能修改比自己角色级别高的用户'];
        }

        if ($newLevel > $operatorLevel) {
            return ['code' => 2005, 'message' => '不能赋予高于自己角色级别的角色'];
        }

        return null;
    }
}
