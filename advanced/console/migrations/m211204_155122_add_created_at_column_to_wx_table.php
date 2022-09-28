<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wx}}`.
 */
class m211204_155122_add_created_at_column_to_wx_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wx}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wx}}', 'created_at');
    }
}
