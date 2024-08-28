<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_code}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%code}}`
 */
class m240826_025712_create_verse_code_table extends Migration
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
        $this->createTable('{{%verse_code}}', [
            'id' => $this->primaryKey(),
            'blockly' => $this->json(),
            'verse_id' => $this->integer()->notNull()->unique(),
            'code_id' => $this->integer()->unique(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_code-verse_id}}',
            '{{%verse_code}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_code-verse_id}}',
            '{{%verse_code}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `code_id`
        $this->createIndex(
            '{{%idx-verse_code-code_id}}',
            '{{%verse_code}}',
            'code_id'
        );

        // add foreign key for table `{{%code}}`
        $this->addForeignKey(
            '{{%fk-verse_code-code_id}}',
            '{{%verse_code}}',
            'code_id',
            '{{%code}}',
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
            '{{%fk-verse_code-verse_id}}',
            '{{%verse_code}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_code-verse_id}}',
            '{{%verse_code}}'
        );

        // drops foreign key for table `{{%code}}`
        $this->dropForeignKey(
            '{{%fk-verse_code-code_id}}',
            '{{%verse_code}}'
        );

        // drops index for column `code_id`
        $this->dropIndex(
            '{{%idx-verse_code-code_id}}',
            '{{%verse_code}}'
        );

        $this->dropTable('{{%verse_code}}');
    }
}
