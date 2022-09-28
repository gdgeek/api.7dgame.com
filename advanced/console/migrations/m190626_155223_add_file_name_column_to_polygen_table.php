<?php

use yii\db\Migration;

/**
 * Handles adding file_name to table `{{%polygen}}`.
 */
class m190626_155223_add_file_name_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'file_name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%polygen}}', 'file_name');
    }
}
