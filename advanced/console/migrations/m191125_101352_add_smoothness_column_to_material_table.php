<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 */
class m191125_101352_add_smoothness_column_to_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'smoothness', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%material}}', 'smoothness');
    }
}
