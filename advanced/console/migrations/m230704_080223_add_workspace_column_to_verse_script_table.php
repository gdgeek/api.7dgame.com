<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_script}}`.
 */
class m230704_080223_add_workspace_column_to_verse_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_script}}', 'workspace', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_script}}', 'workspace');
    }
}
