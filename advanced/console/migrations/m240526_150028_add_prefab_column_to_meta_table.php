<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 */
class m240526_150028_add_prefab_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'prefab', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta}}', 'prefab');
    }
}
