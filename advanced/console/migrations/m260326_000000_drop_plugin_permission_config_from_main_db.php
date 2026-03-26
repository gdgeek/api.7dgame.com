<?php

use yii\db\Migration;

/**
 * 从主库（db）删除 plugin_permission_config 表。
 * 该表的实际数据存储在 pluginDb（bujiaban_plugin）中，
 * 主库中的同名表由早期迁移误建，现予以清理。
 */
class m260326_000000_drop_plugin_permission_config_from_main_db extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%plugin_permission_config}}') !== null) {
            $this->dropTable('{{%plugin_permission_config}}');
        }
    }

    public function safeDown()
    {
        $this->createTable('{{%plugin_permission_config}}', [
            'id' => $this->primaryKey(),
            'role_or_permission' => $this->string(64)->notNull()->comment('RBAC 角色或权限名称'),
            'plugin_name' => $this->string(128)->notNull()->comment('插件标识'),
            'action' => $this->string(128)->notNull()->comment('允许的操作'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx_role_plugin_action',
            '{{%plugin_permission_config}}',
            ['role_or_permission', 'plugin_name', 'action'],
            true
        );
    }
}
