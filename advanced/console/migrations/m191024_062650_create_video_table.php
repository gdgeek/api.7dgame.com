<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%video}}`.
 */
class m191024_062650_create_video_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%video}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'file_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);
        
        // add foreign key for table `file`
        $this->addForeignKey(
            'fk-video-file_id',
            'video',
            'file_id',
            'file',
            'id',
            'CASCADE'
        );
        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-video-user_id',
            'video',
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
               // drops foreign key for table `file`
        $this->dropForeignKey(
            'fk-video-file_id',
            'video'
        );
        $this->dropForeignKey(
            'fk-vidio-user_id',
            'video'
        );
    
        $this->dropTable('{{%video}}');
    }
}
