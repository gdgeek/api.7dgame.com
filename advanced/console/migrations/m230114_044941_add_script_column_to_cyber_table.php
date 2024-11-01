<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%cyber}}`.
 */
class m230114_044941_add_script_column_to_cyber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%cyber}}', 'script', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%cyber}}', 'script');
    }
}
