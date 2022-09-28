<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%info}}`.
 */
class m190621_053008_create_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%info}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(10)->notNull(),
            'company' => $this->string(50)->notNull(),
            'tel' => $this->string(11)->notNull()->unique(),
            'reason' => $this->string()->notNull(),
			'invitation' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%info}}');
    }
}
