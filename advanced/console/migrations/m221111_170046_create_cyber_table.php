<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cyber}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%meta}}`
 */
class m221111_170046_create_cyber_table extends Migration
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
        $this->createTable('{{%cyber}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'meta_id' => $this->integer(),
            'create_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'data' => $this->json(),
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-cyber-author_id}}',
            '{{%cyber}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-cyber-author_id}}',
            '{{%cyber}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-cyber-updater_id}}',
            '{{%cyber}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-cyber-updater_id}}',
            '{{%cyber}}',
            'updater_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-cyber-meta_id}}',
            '{{%cyber}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-cyber-meta_id}}',
            '{{%cyber}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-cyber-author_id}}',
            '{{%cyber}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-cyber-author_id}}',
            '{{%cyber}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-cyber-updater_id}}',
            '{{%cyber}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-cyber-updater_id}}',
            '{{%cyber}}'
        );

        // drops foreign key for table `{{%meta}}`
        $this->dropForeignKey(
            '{{%fk-cyber-meta_id}}',
            '{{%cyber}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-cyber-meta_id}}',
            '{{%cyber}}'
        );

        $this->dropTable('{{%cyber}}');
    }
}
