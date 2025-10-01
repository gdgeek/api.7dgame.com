<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%manager}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m251001_104121_create_manager_table extends Migration
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
        $this->createTable('{{%manager}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'data' => $this->json(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-manager-verse_id}}',
            '{{%manager}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-manager-verse_id}}',
            '{{%manager}}',
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
            '{{%fk-manager-verse_id}}',
            '{{%manager}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-manager-verse_id}}',
            '{{%manager}}'
        );

        $this->dropTable('{{%manager}}');
    }
}
