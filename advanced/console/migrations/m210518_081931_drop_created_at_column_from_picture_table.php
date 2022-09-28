<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%picture}}`.
 */
class m210518_081931_drop_created_at_column_from_picture_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%picture}}', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%picture}}', 'created_at', $this->dateTime());
    }
}
