<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m220403_064651_create_verse_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%verse_info}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'skey' => $this->string(),
            'value' => $this->string(),
        ]);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_info-verse_id}}',
            '{{%verse_info}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_info-verse_id}}',
            '{{%verse_info}}',
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
            '{{%fk-verse_info-verse_id}}',
            '{{%verse_info}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_info-verse_id}}',
            '{{%verse_info}}'
        );

        $this->dropTable('{{%verse_info}}');
    }
}
