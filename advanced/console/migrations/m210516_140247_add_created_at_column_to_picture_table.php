<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%picture}}`.
 */
class m210516_140247_add_created_at_column_to_picture_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%picture}}', 'created_at', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%picture}}', 'created_at');
    }
}
