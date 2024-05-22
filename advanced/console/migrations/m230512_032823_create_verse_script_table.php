<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_script}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m230512_032823_create_verse_script_table extends Migration
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
        $this->createTable('{{%verse_script}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime(),
            'verse_id' => $this->integer()->notNull(),
            'script' => $this->text(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_script-verse_id}}',
            '{{%verse_script}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_script-verse_id}}',
            '{{%verse_script}}',
            'verse_id',
            '{{%verse}}',
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
            '{{%fk-verse_script-verse_id}}',
            '{{%verse_script}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_script-verse_id}}',
            '{{%verse_script}}'
        );

        $this->dropTable('{{%verse_script}}');
    }
}
