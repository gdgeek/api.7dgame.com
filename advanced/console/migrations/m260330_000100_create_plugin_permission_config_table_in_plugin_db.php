<?php

use yii\db\Migration;

class m260330_000100_create_plugin_permission_config_table_in_plugin_db extends Migration
{
    public $db = 'pluginDb';

    private bool $_skip = false;

    public function init()
    {
        if (!\Yii::$app->has('pluginDb')) {
            // CI 环境没有 pluginDb，标记跳过
            $this->_skip = true;
            $this->db = \Yii::$app->db; // 用默认 db 让 parent::init() 不报错
            parent::init();
            return;
        }
        $this->db = 'pluginDb';
        parent::init();
    }

    public function safeUp()
    {
        if ($this->_skip) {
            echo "    > pluginDb not configured, skipping.\n";
            return;
        }

        if ($this->db->schema->getTableSchema('{{%plugin_permission_config}}') !== null) {
            return;
        }

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

    public function safeDown()
    {
        if ($this->_skip) {
            echo "    > pluginDb not configured, skipping.\n";
            return;
        }

        if ($this->db->schema->getTableSchema('{{%plugin_permission_config}}') !== null) {
            $this->dropTable('{{%plugin_permission_config}}');
        }
    }
}