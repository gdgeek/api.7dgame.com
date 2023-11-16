<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%knight}}`.
 */
class m231113_111257_add_limit_column_to_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%knight}}', 'limit', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%knight}}', 'limit');
    }
}
