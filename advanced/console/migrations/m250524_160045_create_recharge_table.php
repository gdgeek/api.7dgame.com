<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%recharge}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%activation}}`
 */
class m250524_160045_create_recharge_table extends Migration
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
        $this->createTable('{{%recharge}}', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull(),
            'duration' => $this->integer()->notNull(),
            'activation_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        // creates index for column `activation_id`
        $this->createIndex(
            '{{%idx-recharge-activation_id}}',
            '{{%recharge}}',
            'activation_id'
        );

        // add foreign key for table `{{%activation}}`
        $this->addForeignKey(
            '{{%fk-recharge-activation_id}}',
            '{{%recharge}}',
            'activation_id',
            '{{%activation}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%activation}}`
        $this->dropForeignKey(
            '{{%fk-recharge-activation_id}}',
            '{{%recharge}}'
        );

        // drops index for column `activation_id`
        $this->dropIndex(
            '{{%idx-recharge-activation_id}}',
            '{{%recharge}}'
        );

        $this->dropTable('{{%recharge}}');
    }
}
