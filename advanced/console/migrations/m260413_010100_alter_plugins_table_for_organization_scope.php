<?php

use yii\db\Migration;

class m260413_010100_alter_plugins_table_for_organization_scope extends Migration
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

        $table = $this->db->schema->getTableSchema('{{%plugins}}');
        if ($table === null) {
            return;
        }

        if ($table->getColumn('organization_name') === null) {
            $this->addColumn('{{%plugins}}', 'organization_name', $this->string(64)->null()->after('version'));
        }

        try {
            $this->createIndex('idx_plugins_organization_name', '{{%plugins}}', 'organization_name');
        } catch (\Throwable $e) {
        }

        if ($table->getColumn('domain') !== null) {
            try {
                $this->dropIndex('idx_plugins_domain', '{{%plugins}}');
            } catch (\Throwable $e) {
            }
            $this->dropColumn('{{%plugins}}', 'domain');
        }

        if ($table->getColumn('group_id') !== null) {
            $this->dropColumn('{{%plugins}}', 'group_id');
        }
    }

    public function safeDown()
    {
        if ($this->_skip) {
            return;
        }

        $table = $this->db->schema->getTableSchema('{{%plugins}}');
        if ($table === null) {
            return;
        }

        if ($table->getColumn('group_id') === null) {
            $this->addColumn('{{%plugins}}', 'group_id', $this->string(64)->null()->after('icon'));
        }

        if ($table->getColumn('domain') === null) {
            $this->addColumn('{{%plugins}}', 'domain', $this->string(255)->null()->after('version'));
            $this->createIndex('idx_plugins_domain', '{{%plugins}}', 'domain');
        }

        if ($table->getColumn('organization_name') !== null) {
            try {
                $this->dropIndex('idx_plugins_organization_name', '{{%plugins}}');
            } catch (\Throwable $e) {
            }
            $this->dropColumn('{{%plugins}}', 'organization_name');
        }
    }
}
