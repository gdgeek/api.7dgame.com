<?php

use yii\db\Migration;

class m260413_000200_create_user_organization_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%user_organization}}') !== null) {
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_organization}}', [
            'user_id' => $this->integer()->notNull(),
            'organization_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('{{%pk-user_organization}}', '{{%user_organization}}', ['user_id', 'organization_id']);
        $this->createIndex('{{%idx-user_organization-organization_id}}', '{{%user_organization}}', 'organization_id');
        $this->addForeignKey(
            '{{%fk-user_organization-user_id}}',
            '{{%user_organization}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%fk-user_organization-organization_id}}',
            '{{%user_organization}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user_organization}}') === null) {
            return;
        }

        $this->dropForeignKey('{{%fk-user_organization-organization_id}}', '{{%user_organization}}');
        $this->dropForeignKey('{{%fk-user_organization-user_id}}', '{{%user_organization}}');
        $this->dropIndex('{{%idx-user_organization-organization_id}}', '{{%user_organization}}');
        $this->dropTable('{{%user_organization}}');
    }
}
