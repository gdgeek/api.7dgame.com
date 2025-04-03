<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%cyber}}`.
 */
class m250403_081849_drop_cyber_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%cyber}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%cyber}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
