<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_script}}`.
 */
class m230514_123243_add_blockly_column_to_verse_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_script}}', 'blockly', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_script}}', 'blockly');
    }
}
