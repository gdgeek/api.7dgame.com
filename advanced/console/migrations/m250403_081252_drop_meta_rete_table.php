<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%meta_rete}}`.
 */
class m250403_081252_drop_meta_rete_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%meta_rete}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%meta_rete}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
