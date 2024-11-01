<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 */
class m240421_013700_add_custom_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'custom', $this->boolean()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta}}', 'custom');
    }
}
