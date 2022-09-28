<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%maker}}`.
 */
class m200304_152048_drop_config_column_logic_column_from_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%maker}}', 'logic');
        $this->dropColumn('{{%maker}}', 'config');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%maker}}', 'logic', $this->text());
        $this->addColumn('{{%maker}}', 'config', $this->text());
    }
}
