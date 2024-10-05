<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%ai_rodin}}`.
 */
class m241003_162716_drop_prompt_column_from_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%ai_rodin}}', 'prompt');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%ai_rodin}}', 'prompt', $this->string());
    }
}
