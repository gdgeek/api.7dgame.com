<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%md5_index_from_file}}`.
 */
class m191023_094253_drop_md5_index_from_file_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('md5','{{%file}}');
        $this->createIndex('md5','{{%file}}',['md5'],false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('md5','{{%file}}');
        $this->createIndex('md5','{{%file}}',['md5'],true);
    }
}
