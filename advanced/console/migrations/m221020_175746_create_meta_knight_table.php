<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta_knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%knight}}`
 * - `{{%user}}`
 */
class m221020_175746_create_meta_knight_table extends Migration
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
        $this->createTable('{{%meta_knight}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'knight_id' => $this->integer(),
            'user_id' => $this->integer()->notNull(),
            'info' => $this->json(),
            'create_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-meta_knight-verse_id}}',
            '{{%meta_knight}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-meta_knight-verse_id}}',
            '{{%meta_knight}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `knight_id`
        $this->createIndex(
            '{{%idx-meta_knight-knight_id}}',
            '{{%meta_knight}}',
            'knight_id'
        );

        // add foreign key for table `{{%knight}}`
        $this->addForeignKey(
            '{{%fk-meta_knight-knight_id}}',
            '{{%meta_knight}}',
            'knight_id',
            '{{%knight}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-meta_knight-user_id}}',
            '{{%meta_knight}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-meta_knight-user_id}}',
            '{{%meta_knight}}',
            'user_id',
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
            '{{%fk-meta_knight-verse_id}}',
            '{{%meta_knight}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-meta_knight-verse_id}}',
            '{{%meta_knight}}'
        );

        // drops foreign key for table `{{%knight}}`
        $this->dropForeignKey(
            '{{%fk-meta_knight-knight_id}}',
            '{{%meta_knight}}'
        );

        // drops index for column `knight_id`
        $this->dropIndex(
            '{{%idx-meta_knight-knight_id}}',
            '{{%meta_knight}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-meta_knight-user_id}}',
            '{{%meta_knight}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-meta_knight-user_id}}',
            '{{%meta_knight}}'
        );

        $this->dropTable('{{%meta_knight}}');
    }
}
