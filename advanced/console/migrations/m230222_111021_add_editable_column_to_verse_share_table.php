<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_share}}`.
 */
class m230222_111021_add_editable_column_to_verse_share_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_share}}', 'editable', $this->boolean()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_share}}', 'editable');
    }
}
