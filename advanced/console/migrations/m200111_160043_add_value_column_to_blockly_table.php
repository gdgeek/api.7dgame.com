<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%blockly}}`.
 */
class m200111_160043_add_value_column_to_blockly_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%blockly}}', 'value', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%blockly}}', 'value');
    }
}
