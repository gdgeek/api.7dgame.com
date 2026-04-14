<?php

use yii\db\Migration;

class m260412_000100_register_user_management_rbac_permissions extends Migration
{
    private const PERMISSIONS = [
        'user-management.list-users',
        'user-management.create-user',
        'user-management.update-user',
        'user-management.delete-user',
        'user-management.change-role',
        'user-management.manage-invitations',
        'user-management.view-user',
    ];

    private const ROLES = ['admin', 'root'];

    public function safeUp()
    {
        $now = time();

        foreach (self::ROLES as $role) {
            $this->upsert('{{%auth_item}}', [
                'name' => $role,
                'type' => 1,
                'description' => $role,
                'created_at' => $now,
                'updated_at' => $now,
            ], false);
        }

        foreach (self::PERMISSIONS as $permission) {
            $this->upsert('{{%auth_item}}', [
                'name' => $permission,
                'type' => 2,
                'description' => $permission,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'type' => 2,
                'description' => $permission,
                'updated_at' => $now,
            ]);
        }

        foreach (self::ROLES as $role) {
            foreach (self::PERMISSIONS as $permission) {
                $this->upsert('{{%auth_item_child}}', [
                    'parent' => $role,
                    'child' => $permission,
                ], false);
            }
        }
    }

    public function safeDown()
    {
        foreach (self::ROLES as $role) {
            $this->delete('{{%auth_item_child}}', [
                'parent' => $role,
                'child' => self::PERMISSIONS,
            ]);
        }

        $this->delete('{{%auth_item}}', [
            'name' => self::PERMISSIONS,
        ]);
    }
}
