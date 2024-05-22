<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%verse_script}}`.
 */
class m230704_083610_drop_blockly_column_from_verse_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%verse_script}}', 'blockly');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%verse_script}}', 'blockly', $this->json());
    }
}
