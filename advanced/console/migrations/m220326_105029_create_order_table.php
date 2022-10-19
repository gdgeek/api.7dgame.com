<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%trade}}`
 */
class m220326_105029_create_order_table extends Migration
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

        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'prepay_id' => $this->string()->unique(),
            'trade_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->timestamp(),
            'state' => $this->integer()->defaultValue(0),
            'payed_time' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `trade_id`
        $this->createIndex(
            '{{%idx-order-trade_id}}',
            '{{%order}}',
            'trade_id'
        );

        // add foreign key for table `{{%trade}}`
        $this->addForeignKey(
            '{{%fk-order-trade_id}}',
            '{{%order}}',
            'trade_id',
            '{{%trade}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-order-user_id}}',
            '{{%order}}',
            'user_id'
        );

// add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-order-user_id}}',
            '{{%order}}',
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
        // drops foreign key for table `{{%trade}}`
        $this->dropForeignKey(
            '{{%fk-order-trade_id}}',
            '{{%order}}'
        );

        // drops index for column `trade_id`
        $this->dropIndex(
            '{{%idx-order-trade_id}}',
            '{{%order}}'
        );

        // drops foreign key for table `{{%trade}}`
        $this->dropForeignKey(
            '{{%fk-order-user_id}}',
            '{{%order}}'
        );

// drops index for column `trade_id`
        $this->dropIndex(
            '{{%idx-order-user_id}}',
            '{{%order}}'
        );

        $this->dropTable('{{%order}}');
    }
}
