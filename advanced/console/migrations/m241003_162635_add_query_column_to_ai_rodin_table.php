<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ai_rodin}}`.
 */
class m241003_162635_add_query_column_to_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ai_rodin}}', 'query', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ai_rodin}}', 'query');
    }
}
