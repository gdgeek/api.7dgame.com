<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%tags}}`.
 */
class m250330_112524_drop_info_column_managed_column_from_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%tags}}', 'info');
        $this->dropColumn('{{%tags}}', 'managed');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%tags}}', 'info', $this->json());
        $this->addColumn('{{%tags}}', 'managed', $this->integer());
    }
}
