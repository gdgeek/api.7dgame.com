<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%maker}}`.
 */
class m200204_162610_drop_maker_column_from_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%maker}}', 'maker');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%maker}}', 'maker', $this->text());
    }
}
