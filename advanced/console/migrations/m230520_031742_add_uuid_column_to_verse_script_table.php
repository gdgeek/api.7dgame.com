<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse_script}}`.
 */
class m230520_031742_add_uuid_column_to_verse_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse_script}}', 'uuid', $this->string()->notNull()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse_script}}', 'uuid');
    }
}
