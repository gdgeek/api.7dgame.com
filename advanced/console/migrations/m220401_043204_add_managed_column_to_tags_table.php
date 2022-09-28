<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%tags}}`.
 */
class m220401_043204_add_managed_column_to_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tags}}', 'managed', $this->boolean()->defaultValue(0)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tags}}', 'managed');
    }
}
