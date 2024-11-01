<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%knight}}`.
 */
class m230701_075935_add_type_column_to_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%knight}}', 'type', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%knight}}', 'type');
    }
}
