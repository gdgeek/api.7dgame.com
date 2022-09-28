<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%maker}}`.
 */
class m200206_181528_add_data_column_tomaker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%maker}}', 'data', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%maker}}', 'data');
    }
}
