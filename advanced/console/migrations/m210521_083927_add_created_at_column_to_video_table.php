<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%video}}`.
 */
class m210521_083927_add_created_at_column_to_video_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%video}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%video}}', 'created_at');
    }
}
