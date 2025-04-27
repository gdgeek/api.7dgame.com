<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%watermark}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250414_050402_create_watermark_table extends Migration
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
        $this->createTable('{{%watermark}}', [
            'id' => $this->primaryKey(),
            'sn' => $this->string()->notNull()->unique(),
            'hardware' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-watermark-user_id}}',
            '{{%watermark}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-watermark-user_id}}',
            '{{%watermark}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-watermark-user_id}}',
            '{{%watermark}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-watermark-user_id}}',
            '{{%watermark}}'
        );

        $this->dropTable('{{%watermark}}');
    }
}
