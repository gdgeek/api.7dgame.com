<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ai_rodin}}`.
 */
class m241004_161423_add_name_column_to_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ai_rodin}}', 'name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ai_rodin}}', 'name');
    }
}
