<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%phototype}}`.
 */
class m250924_155940_add_type_column_to_phototype_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%phototype}}', 'type', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%phototype}}', 'type');
    }
}
