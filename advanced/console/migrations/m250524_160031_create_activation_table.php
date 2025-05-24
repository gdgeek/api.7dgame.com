<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activation}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%device}}`
 */
class m250524_160031_create_activation_table extends Migration
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
        $this->createTable('{{%activation}}', [
            'id' => $this->primaryKey(),
            'begin' => $this->dateTime()->notNull(),
            'duration' => $this->integer()->notNull(),
            'device_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        // creates index for column `device_id`
        $this->createIndex(
            '{{%idx-activation-device_id}}',
            '{{%activation}}',
            'device_id'
        );

        // add foreign key for table `{{%device}}`
        $this->addForeignKey(
            '{{%fk-activation-device_id}}',
            '{{%activation}}',
            'device_id',
            '{{%device}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%device}}`
        $this->dropForeignKey(
            '{{%fk-activation-device_id}}',
            '{{%activation}}'
        );

        // drops index for column `device_id`
        $this->dropIndex(
            '{{%idx-activation-device_id}}',
            '{{%activation}}'
        );

        $this->dropTable('{{%activation}}');
    }
}
