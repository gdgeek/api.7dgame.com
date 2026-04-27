<?php

use yii\db\Migration;

class m260427_010000_register_ar_slam_space_rbac_permissions extends Migration
{
    private const GROUP_ITEMS = [
        '基础操作' => '任何登陆网站的人都可以进行的操作，包括用户资料，上传资源，创建工程和组件，等等',
        '自有空间' => '管理自己的空间',
        '绑定权限' => '绑定规则',
    ];

    private const ROLES = [
        'user' => '基本权限,  模型管理，工程管理',
    ];

    private const ROUTE_PERMISSIONS = [
        '@restful/v1/space/*',
        '@restful/v1/space/create',
        '@restful/v1/space/delete',
        '@restful/v1/space/index',
        '@restful/v1/space/options',
        '@restful/v1/space/update',
        '@restful/v1/space/view',
        '@restful/v1/verse-space/*',
        '@restful/v1/verse-space/create',
        '@restful/v1/verse-space/delete',
        '@restful/v1/verse-space/index',
        '@restful/v1/verse-space/options',
        '@restful/v1/verse-space/update',
        '@restful/v1/verse-space/view',
        '@restful/v1/plugin-ar-slam-localization/*',
        '@restful/v1/plugin-ar-slam-localization/bindings',
        '@restful/v1/plugin-ar-slam-localization/create-bindings',
        '@restful/v1/plugin-ar-slam-localization/options',
    ];

    private const PARENT_CHILDREN = [
        'user' => [
            '基础操作',
        ],
        '基础操作' => [
            '绑定权限',
            '自有空间',
            '@restful/v1/space/create',
            '@restful/v1/space/index',
        ],
        '自有空间' => [
            '@restful/v1/space/delete',
            '@restful/v1/space/update',
            '@restful/v1/space/view',
        ],
        '绑定权限' => [
            '@restful/v1/verse-space/create',
            '@restful/v1/verse-space/delete',
            '@restful/v1/verse-space/index',
            '@restful/v1/verse-space/update',
            '@restful/v1/verse-space/view',
            '@restful/v1/plugin-ar-slam-localization/bindings',
            '@restful/v1/plugin-ar-slam-localization/create-bindings',
        ],
    ];

    public function safeUp()
    {
        $now = time();

        foreach (self::ROLES as $role => $description) {
            $this->upsert('{{%auth_item}}', [
                'name' => $role,
                'type' => 1,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ], false);
        }

        foreach (self::GROUP_ITEMS as $name => $description) {
            $this->upsert('{{%auth_item}}', [
                'name' => $name,
                'type' => 2,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ], false);
        }

        foreach (self::ROUTE_PERMISSIONS as $permission) {
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

        foreach (self::PARENT_CHILDREN as $parent => $children) {
            foreach ($children as $child) {
                $this->upsert('{{%auth_item_child}}', [
                    'parent' => $parent,
                    'child' => $child,
                ], false);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%auth_item_child}}', [
            'parent' => '绑定权限',
            'child' => [
                '@restful/v1/verse-space/index',
                '@restful/v1/verse-space/view',
                '@restful/v1/plugin-ar-slam-localization/bindings',
                '@restful/v1/plugin-ar-slam-localization/create-bindings',
            ],
        ]);

        $this->delete('{{%auth_item}}', [
            'name' => [
                '@restful/v1/plugin-ar-slam-localization/*',
                '@restful/v1/plugin-ar-slam-localization/bindings',
                '@restful/v1/plugin-ar-slam-localization/create-bindings',
                '@restful/v1/plugin-ar-slam-localization/options',
            ],
        ]);
    }
}
