<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vp_map}}`.
 */
class m240728_163216_create_vp_map_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%vp_map}}', [
            'id' => $this->primaryKey(),
            'page' => $this->integer()->notNull()->unique(),
            'info' => $this->json(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vp_map}}');
    }
}
