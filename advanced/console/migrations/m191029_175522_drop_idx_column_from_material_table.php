<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%material}}`.
 */
class m191029_175522_drop_idx_column_from_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%material}}', 'idx');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%material}}', 'idx', $this->integer());
    }
}
