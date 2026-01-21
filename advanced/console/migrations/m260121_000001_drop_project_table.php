<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%project}}`.
 * 
 * This migration drops the project table and all its related foreign keys.
 * 
 * Related tables that have foreign keys to project:
 * - logic (fk-logic-project_id)
 * 
 * Foreign keys in project table:
 * - fk-project-user_id (to user table)
 * - fk-project-image_id (to file table)
 */
class m260121_000001_drop_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // First, drop foreign keys from other tables that reference project
        
        // Drop foreign key from logic table if it exists
        if ($this->db->schema->getTableSchema('{{%logic}}', true) !== null) {
            try {
                $this->dropForeignKey('{{%fk-logic-project_id}}', '{{%logic}}');
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
                echo "Note: Foreign key fk-logic-project_id not found or already dropped.\n";
            }
        }
        
        // Drop foreign keys from project table itself
        try {
            $this->dropForeignKey('{{%fk-project-user_id}}', '{{%project}}');
        } catch (\Exception $e) {
            echo "Note: Foreign key fk-project-user_id not found or already dropped.\n";
        }
        
        try {
            $this->dropForeignKey('{{%fk-project-image_id}}', '{{%project}}');
        } catch (\Exception $e) {
            echo "Note: Foreign key fk-project-image_id not found or already dropped.\n";
        }
        
        // Drop indexes
        try {
            $this->dropIndex('{{%idx-project-user_id}}', '{{%project}}');
        } catch (\Exception $e) {
            echo "Note: Index idx-project-user_id not found or already dropped.\n";
        }
        
        try {
            $this->dropIndex('{{%idx-project-image_id}}', '{{%project}}');
        } catch (\Exception $e) {
            echo "Note: Index idx-project-image_id not found or already dropped.\n";
        }
        
        // Finally, drop the table
        $this->dropTable('{{%project}}');
        
        echo "Project table and all related foreign keys have been dropped successfully.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Recreate the project table with all its columns
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'logic' => $this->text(),
            'configure' => $this->text(),
            'user_id' => $this->integer(),
            'sharing' => $this->boolean(),
            'image_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'name' => $this->string(),
            'introduce' => $this->text(),
            'programme_id' => $this->integer(),
        ], $tableOptions);
        
        // Recreate indexes
        $this->createIndex(
            '{{%idx-project-user_id}}',
            '{{%project}}',
            'user_id'
        );
        
        $this->createIndex(
            '{{%idx-project-image_id}}',
            '{{%project}}',
            'image_id'
        );
        
        // Recreate foreign keys
        $this->addForeignKey(
            '{{%fk-project-user_id}}',
            '{{%project}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            '{{%fk-project-image_id}}',
            '{{%project}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );
        
        // Recreate foreign key from logic table
        if ($this->db->schema->getTableSchema('{{%logic}}', true) !== null) {
            $this->addForeignKey(
                '{{%fk-logic-project_id}}',
                '{{%logic}}',
                'project_id',
                '{{%project}}',
                'id',
                'CASCADE'
            );
        }
        
        echo "Project table has been recreated.\n";
    }
}
