<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_cyber}}`.
 */
class m220102_092508_create_verse_cyber_table extends Migration
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

        $this->createTable('{{%verse_cyber}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'verse_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'data' => $this->json(),
            'script' => $this->text(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_cyber-verse_id}}',
            '{{%verse_cyber}}',
            'verse_id'
        );

// add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_cyber-verse_id}}',
            '{{%verse_cyber}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-verse_cyber-author_id}}',
            '{{%verse_cyber}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse_cyber-author_id}}',
            '{{%verse_cyber}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-verse_cyber-updater_id}}',
            '{{%verse_cyber}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse_cyber-updater_id}}',
            '{{%verse_cyber}}',
            'updater_id',
            '{{%user}}',
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
            '{{%fk-verse_cyber-verse_id}}',
            '{{%verse_cyber}}'
        );
        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_cyber-verse_id}}',
            '{{%verse_cyber}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse_cyber-author_id}}',
            '{{%verse_cyber}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-verse_cyber-author_id}}',
            '{{%verse_cyber}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse_cyber-updater_id}}',
            '{{%verse_cyber}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-verse_cyber-updater_id}}',
            '{{%verse_cyber}}'
        );

       
        $this->dropTable('{{%verse_cyber}}');
    }
}
