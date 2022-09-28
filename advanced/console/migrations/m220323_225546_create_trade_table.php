<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%trade}}`.
 */
class m220323_225546_create_trade_table extends Migration
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

        $this->createTable('{{%trade}}', [
            'id' => $this->primaryKey(),
            'out_trade_no' => $this->string()->unique()->notNull(),
            'description' => $this->string()->notNull(),
            'notify_url' => $this->string(),
            'amount' => $this->json()->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%trade}}');
    }
}
