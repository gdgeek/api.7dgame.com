<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%version}}`.
 */
class m210503_174040_create_version_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%version}}', [
            'id' => $this->primaryKey(),
            'version' => $this->integer()->unique(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%version}}');
    }
}
