<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%polygen}}`.
 */
class m211101_071434_add_info_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'info', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%polygen}}', 'info');
    }
}
