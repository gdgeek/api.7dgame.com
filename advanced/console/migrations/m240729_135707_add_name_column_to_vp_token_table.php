<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vp_token}}`.
 */
class m240729_135707_add_name_column_to_vp_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vp_token}}', 'name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vp_token}}', 'name');
    }
}
