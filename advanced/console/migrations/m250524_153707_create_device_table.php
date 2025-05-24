<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%device}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250524_153707_create_device_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%device}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'uuid' => $this->string()->unique(),
            'owner_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        // creates index for column `owner_id`
        $this->createIndex(
            '{{%idx-device-owner_id}}',
            '{{%device}}',
            'owner_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-device-owner_id}}',
            '{{%device}}',
            'owner_id',
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
            '{{%fk-device-owner_id}}',
            '{{%device}}'
        );

        // drops index for column `owner_id`
        $this->dropIndex(
            '{{%idx-device-owner_id}}',
            '{{%device}}'
        );

        $this->dropTable('{{%device}}');
    }
}
