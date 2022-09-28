<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 */
class m200207_072743_add_alpha_column_to_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'alpha', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%material}}', 'alpha');
    }
}
