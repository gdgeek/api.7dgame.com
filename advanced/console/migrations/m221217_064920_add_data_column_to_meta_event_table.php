<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta_event}}`.
 */
class m221217_064920_add_data_column_to_meta_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta_event}}', 'data', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta_event}}', 'data');
    }
}
