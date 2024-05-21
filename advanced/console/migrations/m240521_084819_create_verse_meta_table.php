<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%meta}}`
 */
class m240521_084819_create_verse_meta_table extends Migration
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
        $this->createTable('{{%verse_meta}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'meta_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_meta-verse_id}}',
            '{{%verse_meta}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_meta-verse_id}}',
            '{{%verse_meta}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-verse_meta-meta_id}}',
            '{{%verse_meta}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-verse_meta-meta_id}}',
            '{{%verse_meta}}',
            'meta_id',
            '{{%meta}}',
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
            '{{%fk-verse_meta-verse_id}}',
            '{{%verse_meta}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_meta-verse_id}}',
            '{{%verse_meta}}'
        );

        // drops foreign key for table `{{%meta}}`
        $this->dropForeignKey(
            '{{%fk-verse_meta-meta_id}}',
            '{{%verse_meta}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-verse_meta-meta_id}}',
            '{{%verse_meta}}'
        );

        $this->dropTable('{{%verse_meta}}');
    }
}
