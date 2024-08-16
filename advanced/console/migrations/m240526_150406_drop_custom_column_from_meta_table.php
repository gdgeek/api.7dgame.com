<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 */
class m240526_150406_drop_custom_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta}}', 'custom');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'custom', $this->boolean());
    }
}
