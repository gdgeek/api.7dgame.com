<?php

use yii\db\Migration;

class m260330_000200_create_plugin_menu_groups_table_in_plugin_db extends Migration
{
    public $db = 'pluginDb';

    public function init()
    {
        $this->db = 'pluginDb';
        parent::init();
    }

    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%plugin_menu_groups}}') !== null) {
            return;
        }

        $this->createTable('{{%plugin_menu_groups}}', [
            'id' => $this->string(64)->notNull()->comment('分组标识，如 tools'),
            'name' => $this->string(128)->notNull()->comment('分组名称'),
            'name_i18n' => $this->json()->null()->comment('多语言名称 {"zh-CN":"","en-US":""}'),
            'icon' => $this->string(64)->null()->comment('Element Plus 图标名'),
            'order' => $this->integer()->notNull()->defaultValue(0)->comment('排序权重'),
            'domain' => $this->string(255)->null()->comment('绑定域名，NULL 为默认配置'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addPrimaryKey('pk_plugin_menu_groups_id', '{{%plugin_menu_groups}}', 'id');
        $this->createIndex('idx_plugin_menu_groups_domain', '{{%plugin_menu_groups}}', 'domain');
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%plugin_menu_groups}}') !== null) {
            $this->dropTable('{{%plugin_menu_groups}}');
        }
    }
}