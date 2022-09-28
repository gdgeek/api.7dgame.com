<?php

use yii\db\Migration;

/**
 * Handles adding introduce to table `{{%project}}`.
 */
class m190606_162627_add_introduce_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'introduce', $this->string(140));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'introduce');
    }
}
