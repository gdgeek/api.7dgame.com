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
        $this->createTable('{{%verse_code}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull()->unique(),
            'code_id' => $this->integer()->unique(),
        ]);

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
