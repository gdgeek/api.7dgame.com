<?php

use yii\db\Migration;

/**
 * Normalizes verse_tags to the tags_id column expected by the application.
 */
class m260422_000001_rename_verse_tags_tag_id_to_tags_id extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%verse_tags}}', true);
        if ($table === null || isset($table->columns['tags_id']) || !isset($table->columns['tag_id'])) {
            return;
        }

        $this->dropForeignKeyIfExists('{{%fk-verse_tags-tag_id}}', '{{%verse_tags}}');
        $this->dropIndexIfExists('{{%idx-verse_tags-tag_id}}', '{{%verse_tags}}');
        $this->renameColumn('{{%verse_tags}}', 'tag_id', 'tags_id');
        $this->createIndex('{{%idx-verse_tags-tags_id}}', '{{%verse_tags}}', 'tags_id');
        $this->addForeignKey(
            '{{%fk-verse_tags-tags_id}}',
            '{{%verse_tags}}',
            'tags_id',
            '{{%tags}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%verse_tags}}', true);
        if ($table === null || isset($table->columns['tag_id']) || !isset($table->columns['tags_id'])) {
            return;
        }

        $this->dropForeignKeyIfExists('{{%fk-verse_tags-tags_id}}', '{{%verse_tags}}');
        $this->dropIndexIfExists('{{%idx-verse_tags-tags_id}}', '{{%verse_tags}}');
        $this->renameColumn('{{%verse_tags}}', 'tags_id', 'tag_id');
        $this->createIndex('{{%idx-verse_tags-tag_id}}', '{{%verse_tags}}', 'tag_id');
        $this->addForeignKey(
            '{{%fk-verse_tags-tag_id}}',
            '{{%verse_tags}}',
            'tag_id',
            '{{%tags}}',
            'id',
            'CASCADE'
        );
    }

    private function dropForeignKeyIfExists(string $name, string $table): void
    {
        try {
            $this->dropForeignKey($name, $table);
        } catch (\Throwable $e) {
            echo "Note: Foreign key {$name} not found or already dropped.\n";
        }
    }

    private function dropIndexIfExists(string $name, string $table): void
    {
        try {
            $this->dropIndex($name, $table);
        } catch (\Throwable $e) {
            echo "Note: Index {$name} not found or already dropped.\n";
        }
    }
}
