<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%trade}}`.
 */
class m250403_075314_drop_trade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%trade}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%trade}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
