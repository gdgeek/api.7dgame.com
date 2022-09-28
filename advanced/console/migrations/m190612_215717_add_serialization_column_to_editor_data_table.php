<?php

use yii\db\Migration;

/**
 * Handles adding serialization to table `{{%editor_data}}`.
 */
class m190612_215717_add_serialization_column_to_editor_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%editor_data}}', 'serialization', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%editor_data}}', 'serialization');
    }
}
