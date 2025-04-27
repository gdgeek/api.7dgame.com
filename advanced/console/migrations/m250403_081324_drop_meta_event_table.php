<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%meta_event}}`.
 */
class m250403_081324_drop_meta_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%meta_event}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%meta_event}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
