<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%meta_resource}}`.
 */
class m250403_081306_drop_meta_resource_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%meta_resource}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%meta_resource}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
