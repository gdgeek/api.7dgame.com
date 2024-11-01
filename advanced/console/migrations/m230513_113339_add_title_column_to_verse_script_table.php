<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_script}}`.
 */
class m230513_113339_add_title_column_to_verse_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_script}}', 'title', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_script}}', 'title');
    }
}
