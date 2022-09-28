<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%like}}`.
 */
class m220317_090247_add_created_at_column_to_like_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%like}}', 'created_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%like}}', 'created_at');
    }
}
