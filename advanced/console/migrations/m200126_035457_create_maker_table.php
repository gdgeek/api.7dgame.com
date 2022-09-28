<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%maker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m200126_035457_create_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
    	$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%maker}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'maker' => $this->text(),
            'config' => $this->text(),
            'logic' => $this->text(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-maker-user_id}}',
            '{{%maker}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-maker-user_id}}',
            '{{%maker}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-maker-user_id}}',
            '{{%maker}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-maker-user_id}}',
            '{{%maker}}'
        );

        $this->dropTable('{{%maker}}');
    }
}
