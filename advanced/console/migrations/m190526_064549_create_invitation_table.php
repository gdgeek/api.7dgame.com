<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invitation}}`.
 */
class m190526_064549_create_invitation_table extends Migration
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

        $this->createTable('{{%invitation}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->unique(),
            'sender_id' => $this->integer()->notNull(),
            'recipient_id' => $this->integer()->null(),
        ], $tableOptions);

		// creates index for column `author_id`
        $this->createIndex(
            'idx-post-sender_id',
            'invitation',
            'sender_id'
        );

		// add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-sender_id',
            'invitation',
            'sender_id',
            'user',
            'id',
            'CASCADE'
        );

			// add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-recipient_id',
            'invitation',
            'recipient_id',
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
        $this->dropTable('{{%invitation}}');
    }
}
