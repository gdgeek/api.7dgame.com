<?php

use yii\db\Migration;

/**
 * Rebuilds the space data contract and restores verse-space bindings.
 */
class m260427_000000_rebuild_space_and_verse_space_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->schema->getTableSchema('{{%verse_space}}', true) !== null) {
            $this->dropTable('{{%verse_space}}');
        }

        if ($this->db->schema->getTableSchema('{{%space}}', true) !== null) {
            $this->dropTable('{{%space}}');
        }

        $this->createTable('{{%space}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'mesh_id' => $this->integer()->notNull(),
            'image_id' => $this->integer(),
            'file_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'data' => $this->json(),
        ], $tableOptions);

        $this->createIndex('{{%idx-space-user_id}}', '{{%space}}', 'user_id');
        $this->addForeignKey(
            '{{%fk-space-user_id}}',
            '{{%space}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-space-mesh_id}}', '{{%space}}', 'mesh_id');
        $this->addForeignKey(
            '{{%fk-space-mesh_id}}',
            '{{%space}}',
            'mesh_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-space-image_id}}', '{{%space}}', 'image_id');
        $this->addForeignKey(
            '{{%fk-space-image_id}}',
            '{{%space}}',
            'image_id',
            '{{%file}}',
            'id',
            'SET NULL'
        );

        $this->createIndex('{{%idx-space-file_id}}', '{{%space}}', 'file_id');
        $this->addForeignKey(
            '{{%fk-space-file_id}}',
            '{{%space}}',
            'file_id',
            '{{%file}}',
            'id',
            'SET NULL'
        );

        $this->createTable('{{%verse_space}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'space_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
        ], $tableOptions);

        $this->createIndex('{{%idx-verse_space-verse_id-unique}}', '{{%verse_space}}', 'verse_id', true);
        $this->addForeignKey(
            '{{%fk-verse_space-verse_id}}',
            '{{%verse_space}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('{{%idx-verse_space-space_id}}', '{{%verse_space}}', 'space_id');
        $this->addForeignKey(
            '{{%fk-verse_space-space_id}}',
            '{{%verse_space}}',
            'space_id',
            '{{%space}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%verse_space}}', true) !== null) {
            $this->dropTable('{{%verse_space}}');
        }

        if ($this->db->schema->getTableSchema('{{%space}}', true) !== null) {
            $this->dropTable('{{%space}}');
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%space}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'sample_id' => $this->integer()->notNull(),
            'mesh_id' => $this->integer()->notNull(),
            'dat_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
            'name' => $this->string(),
        ], $tableOptions);

        $this->createIndex('{{%idx-space-author_id}}', '{{%space}}', 'author_id');
        $this->addForeignKey('{{%fk-space-author_id}}', '{{%space}}', 'author_id', '{{%user}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-sample_id}}', '{{%space}}', 'sample_id');
        $this->addForeignKey('{{%fk-space-sample_id}}', '{{%space}}', 'sample_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-mesh_id}}', '{{%space}}', 'mesh_id');
        $this->addForeignKey('{{%fk-space-mesh_id}}', '{{%space}}', 'mesh_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-dat_id}}', '{{%space}}', 'dat_id');
        $this->addForeignKey('{{%fk-space-dat_id}}', '{{%space}}', 'dat_id', '{{%file}}', 'id', 'CASCADE');
        $this->createIndex('{{%idx-space-image_id}}', '{{%space}}', 'image_id');
        $this->addForeignKey('{{%fk-space-image_id}}', '{{%space}}', 'image_id', '{{%file}}', 'id', 'CASCADE');
    }
}
