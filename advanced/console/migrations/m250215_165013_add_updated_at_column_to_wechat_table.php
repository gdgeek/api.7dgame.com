<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wechat}}`.
 */
class m250215_165013_add_updated_at_column_to_wechat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wechat}}', 'updated_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wechat}}', 'updated_at');
    }
}
