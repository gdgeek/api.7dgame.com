<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%code}}`.
 */
class m240826_025650_create_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%code}}', [
            'id' => $this->primaryKey(),
            'blockly' => $this->text(),
            'lua' => $this->text(),
            'js' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%code}}');
    }
}
