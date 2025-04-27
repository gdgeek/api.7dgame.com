<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%tags}}`.
 */
class m250323_123614_add_key_column_to_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tags}}', 'key', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tags}}', 'key');
    }
}
