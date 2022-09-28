<?php

use yii\db\Migration;

/**
 * Class m191022_033650_create_picture
 */
class m191022_033650_create_picture extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('picture', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'file_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);

        // creates index for column `file_id`
        $this->createIndex(
            'idx-picture-file_id',
            'picture',
            'file_id'
        );

        // add foreign key for table `file`
        $this->addForeignKey(
            'fk-picture-file_id',
            'picture',
            'file_id',
            'file',
            'id',
            'CASCADE'
        );
        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-picture-user_id',
            'picture',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
        // creates index for column `name`
        $this->createIndex(
            'idx-picture-name',
            'picture',
            'name'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `file`
        $this->dropForeignKey(
            'fk-picture-file_id',
            'picture'
        );
        $this->dropForeignKey(
            'fk-picture-user_id',
            'picture'
        );

        // drops index for column `file_id`
        $this->dropIndex(
            'idx-picture-file_id',
            'picture'
        );

        // drops index for column `name`
        $this->dropIndex(
            'idx-picture-name',
            'picture'
        );

        $this->dropTable('picture');
    }
}
