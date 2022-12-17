<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 */
class m221217_165731_add_uuid_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'uuid', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta}}', 'uuid');
    }
}
