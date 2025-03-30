<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%tags}}`.
 */
class m250330_104436_add_type_column_to_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE `tags` ADD COLUMN `type` ENUM('Domain','Classify', 'Status') NOT NULL DEFAULT 'Classify'");
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE `tags` DROP COLUMN `type`");
       
    }
}
