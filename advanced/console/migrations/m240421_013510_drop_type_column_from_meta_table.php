<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 */
class m240421_013510_drop_type_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'type', $this->string());
    }
}
