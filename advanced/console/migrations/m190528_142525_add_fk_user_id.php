<?php

use yii\db\Migration;

/**
 * Class m190528_142525_add_fk_user_id
 */
class m190528_142525_add_fk_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		// add foreign key for table `user`
        $this->addForeignKey(
            'fk-polygon-user_id',
            'polygon',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       
        $this->dropForeignKey(
            'fk-polygon-user_id',
            'polygon'
        );

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190528_142525_add_fk_user_id cannot be reverted.\n";

        return false;
    }
    */
}
