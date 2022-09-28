<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_type}}`.
 */
class m191120_074714_create_content_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {	$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%content_type}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%content_type}}');
    }
}
