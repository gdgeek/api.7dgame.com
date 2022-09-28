<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%material}}`.
 */
class m191018_081723_create_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%material}}', [
            'id' => $this->primaryKey(),
            'albedo' => $this->string(),
            'metallic' => $this->string(),
            'normal' => $this->string(),
            'occlusion' => $this->string(),
            'emission' => $this->string(),
        ],$tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%material}}');
    }
}
