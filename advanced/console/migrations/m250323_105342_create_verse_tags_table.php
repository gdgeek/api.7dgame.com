<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_tags}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%tags}}`
 */
class m250323_105342_create_verse_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%verse_tags}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'tags_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_tags-verse_id}}',
            '{{%verse_tags}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_tags-verse_id}}',
            '{{%verse_tags}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `tags_id`
        $this->createIndex(
            '{{%idx-verse_tags-tags_id}}',
            '{{%verse_tags}}',
            'tags_id'
        );

        // add foreign key for table `{{%tags}}`
        $this->addForeignKey(
            '{{%fk-verse_tags-tags_id}}',
            '{{%verse_tags}}',
            'tags_id',
            '{{%tags}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-verse_tags-verse_id}}',
            '{{%verse_tags}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_tags-verse_id}}',
            '{{%verse_tags}}'
        );

        // drops foreign key for table `{{%tags}}`
        $this->dropForeignKey(
            '{{%fk-verse_tags-tag_id}}',
            '{{%verse_tags}}'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            '{{%idx-verse_tags-tag_id}}',
            '{{%verse_tags}}'
        );

        $this->dropTable('{{%verse_tags}}');
    }
}
