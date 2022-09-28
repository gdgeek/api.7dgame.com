<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%resource}}`.
 */
class m220511_225137_add_uuid_column_to_resource_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%resource}}', 'uuid', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%resource}}', 'uuid');
    }
}
