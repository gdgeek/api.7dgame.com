<?php

use yii\db\Migration;

/**
 * Handles adding sharing to table `{{%polygen}}`.
 */
class m190722_153757_add_sharing_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'sharing', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%polygen}}', 'sharing');
    }
}
