<?php

use yii\db\Migration;

/**
 * Class m210518_152004_add_indexs_to_table
 */
class m210518_152004_add_indexs_to_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->dropIndex('{{%md5}}', '{{%file}}');
        $this->createIndex('{{%idx-file-md5}}', '{{%file}}', 'md5', false);
        $this->createIndex('{{%idx-file-url}}', '{{%file}}', 'url', false);
        $this->createIndex('{{%idx-file-filename}}', '{{%file}}', 'filename', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210518_152004_add_indexs_to_table cannot be reverted.\n";

        $this->createIndex('{{%md5}}', '{{%file}}', 'md5', false);
        $this->dropIndex('{{%idx-file-md5}}', '{{%file}}');
        $this->dropIndex('{{%idx-file-url}}', '{{%file}}');
        $this->dropIndex('{{%idx-file-filename}}}', '{{%filename}}');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210518_152004_add_indexs_to_table cannot be reverted.\n";

        return false;
    }
    */
}
