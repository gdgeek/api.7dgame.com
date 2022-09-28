<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback_state}}`.
 */
class m190624_105011_create_feedback_describe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback_describe}}', [
            'id' => $this->primaryKey(),
            'describe' => $this->string(128),
            'order' =>$this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%feedback_describe}}');
    }
}
