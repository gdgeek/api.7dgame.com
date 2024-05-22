<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 */
class m240419_080730_add_type_column_events_column_title_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'type', $this->string());
        $this->addColumn('{{%meta}}', 'events', $this->json());
        $this->addColumn('{{%meta}}', 'title', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta}}', 'type');
        $this->dropColumn('{{%meta}}', 'events');
        $this->dropColumn('{{%meta}}', 'title');
    }
}
