<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%tags}}`.
 */
class m220218_062529_drop_type_column_from_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%tags}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%tags}}', 'type', $this->integer());
    }
}
