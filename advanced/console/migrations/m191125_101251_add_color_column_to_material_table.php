<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 */
class m191125_101251_add_color_column_to_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'color', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%material}}', 'color');
    }
}
