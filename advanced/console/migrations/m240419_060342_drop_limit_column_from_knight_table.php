<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%knight}}`.
 */
class m240419_060342_drop_limit_column_from_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%knight}}', 'limit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%knight}}', 'limit', $this->integer());
    }
}
