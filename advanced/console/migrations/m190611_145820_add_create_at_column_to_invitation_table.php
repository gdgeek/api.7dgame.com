<?php

use yii\db\Migration;

/**
 * Handles adding create_at to table `{{%invitation}}`.
 */
class m190611_145820_add_create_at_column_to_invitation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invitation}}', 'create_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invitation}}', 'create_at');
    }
}
