<?php

use yii\db\Migration;

class m260413_010200_drop_plugin_menu_groups_table extends Migration
{
    public $db = 'pluginDb';

    private bool $_skip = false;

    public function init()
    {
        if (!\Yii::$app->has('pluginDb')) {
            $this->_skip = true;
            $this->db = \Yii::$app->db;
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

        if ($this->db->schema->getTableSchema('{{%plugin_menu_groups}}') !== null) {
            $this->dropTable('{{%plugin_menu_groups}}');
        }
    }

    public function safeDown()
    {
        if ($this->_skip) {
            return;
        }

        if ($this->db->schema->getTableSchema('{{%plugin_menu_groups}}') !== null) {
            return;
        }

        $this->createTable('{{%plugin_menu_groups}}', [
            'id' => $this->string(64)->notNull(),
            'name' => $this->string(128)->notNull(),
            'name_i18n' => $this->json()->null(),
            'icon' => $this->string(64)->null(),
            'order' => $this->integer()->notNull()->defaultValue(0),
            'domain' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addPrimaryKey('pk_plugin_menu_groups_id', '{{%plugin_menu_groups}}', 'id');
        $this->createIndex('idx_plugin_menu_groups_domain', '{{%plugin_menu_groups}}', 'domain');
    }
}
