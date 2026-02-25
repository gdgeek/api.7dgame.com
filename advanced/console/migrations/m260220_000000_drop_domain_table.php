<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%domain}}`.
 */
class m260220_000000_drop_domain_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%domain}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%domain}}', [
            'id' => $this->primaryKey(),
            'domain' => $this->string()->notNull()->unique(),
            'title' => $this->string(),
            'author' => $this->string(),
            'description' => $this->string(),
            'keywords' => $this->string(),
            'links' => $this->text(),
        ]);
    }
}
