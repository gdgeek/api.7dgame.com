<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta_event}}`.
 */
class m221216_051444_drop_links_column_from_meta_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta_event}}', 'links');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta_event}}', 'links', $this->json());
    }
}
