<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ai_rodin}}`.
 */
class m240930_132147_add_generation_column_check_column_download_column_to_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ai_rodin}}', 'generation', $this->json());
        $this->addColumn('{{%ai_rodin}}', 'check', $this->json());
        $this->addColumn('{{%ai_rodin}}', 'download', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ai_rodin}}', 'generation');
        $this->dropColumn('{{%ai_rodin}}', 'check');
        $this->dropColumn('{{%ai_rodin}}', 'download');
    }
}
