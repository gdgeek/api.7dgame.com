<?php

use yii\db\Migration;

class m260330_000300_create_plugins_table_in_plugin_db extends Migration
{
    public $db = 'pluginDb';

    public function init()
    {
        $this->db = 'pluginDb';
        parent::init();
    }

    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%plugins}}') !== null) {
            return;
        }

        $this->createTable('{{%plugins}}', [
            'id' => $this->string(64)->notNull()->comment('插件标识，如 user-management'),
            'name' => $this->string(128)->notNull()->comment('插件名称'),
            'name_i18n' => $this->json()->null()->comment('多语言名称'),
            'description' => $this->string(512)->null()->comment('插件描述'),
            'url' => $this->string(512)->notNull()->comment('插件前端 URL'),
            'icon' => $this->string(64)->null()->comment('Element Plus 图标名'),
            'group_id' => $this->string(64)->null()->comment('所属菜单分组 ID'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('是否启用'),
            'order' => $this->integer()->notNull()->defaultValue(0)->comment('排序权重'),
            'allowed_origin' => $this->string(512)->null()->comment('允许的 origin（CORS/postMessage）'),
            'version' => $this->string(32)->null()->comment('插件版本号'),
            'domain' => $this->string(255)->null()->comment('绑定域名，NULL 为默认配置'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addPrimaryKey('pk_plugins_id', '{{%plugins}}', 'id');
        $this->createIndex('idx_plugins_domain', '{{%plugins}}', 'domain');
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%plugins}}') !== null) {
            $this->dropTable('{{%plugins}}');
        }
    }
}