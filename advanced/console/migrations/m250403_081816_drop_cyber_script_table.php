<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%cyber_script}}`.
 */
class m250403_081816_drop_cyber_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%cyber_script}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%cyber_script}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
