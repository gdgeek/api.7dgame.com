<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%ai_rodin}}`.
 */
class m240930_132809_drop_status_column_token_column_from_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%ai_rodin}}', 'status');
        $this->dropColumn('{{%ai_rodin}}', 'token');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%ai_rodin}}', 'status', $this->json());
        $this->addColumn('{{%ai_rodin}}', 'token', $this->string());
    }
}
