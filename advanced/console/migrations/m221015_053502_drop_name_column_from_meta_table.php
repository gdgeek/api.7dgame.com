<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 */
class m221015_053502_drop_name_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta}}', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'name', $this->string());
    }
}
