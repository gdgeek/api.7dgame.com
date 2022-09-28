<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback_state}}`.
 */
class m190624_105005_create_feedback_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback_state}}', [
            'id' => $this->primaryKey(),
            'state' => $this->string(128),
            'order' =>$this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%feedback_state}}');
    }
}
