<?php

use yii\db\Migration;

/**
 * Handles adding url to table `{{%polygen}}`.
 */
class m190818_181028_add_url_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'url', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%polygen}}', 'url');
    }
}
