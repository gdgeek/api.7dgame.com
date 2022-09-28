<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user_info}}`.
 */
class m220329_064116_add_gold_column_points_column_to_user_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_info}}', 'gold', $this->integer()->defaultValue(0)->notNull());
        $this->addColumn('{{%user_info}}', 'points', $this->integer()->defaultValue(0)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user_info}}', 'gold');
        $this->dropColumn('{{%user_info}}', 'points');
    }
}
