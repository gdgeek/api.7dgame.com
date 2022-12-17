<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta_knight}}`.
 */
class m221217_173141_add_uuid_column_to_meta_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta_knight}}', 'uuid', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta_knight}}', 'uuid');
    }
}
