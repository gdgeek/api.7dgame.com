<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%question}}`.
 */
class m190621_052542_create_question_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%question}}', [
            'id' => $this->primaryKey(),
            'question' => $this->string(),
            'options' => $this->string(),
            'answer' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%question}}');
    }
}
