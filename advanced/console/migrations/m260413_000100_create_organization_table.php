<?php

use yii\db\Migration;

class m260413_000100_create_organization_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%organization}}') !== null) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%organization}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'name' => $this->string(64)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('{{%ux-organization-name}}', '{{%organization}}', 'name', true);
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%organization}}') === null) {
            return;
        }

        $this->dropIndex('{{%ux-organization-name}}', '{{%organization}}');
        $this->dropTable('{{%organization}}');
    }
}
