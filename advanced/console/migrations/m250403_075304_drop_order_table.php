<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%order}}`.
 */
class m250403_075304_drop_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%order}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
