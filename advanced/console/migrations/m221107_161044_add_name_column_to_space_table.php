<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%space}}`.
 */
class m221107_161044_add_name_column_to_space_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%space}}', 'name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%space}}', 'name');
    }
}
