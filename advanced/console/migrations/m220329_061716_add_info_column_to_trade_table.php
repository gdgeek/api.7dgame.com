<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trade}}`.
 */
class m220329_061716_add_info_column_to_trade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%trade}}', 'info', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%trade}}', 'info');
    }
}
