<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%polygen}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m190626_094428_create_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%polygen}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128),
            'type' => $this->string(10),
            'user_id' => $this->integer()->notNull(),
            'md5' => $this->string(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);


		// creates index for column `user_id`
        $this->createIndex(
            '{{%idx-polygen-name}}',
            '{{%polygen}}',
            'name'
        );



        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-polygen-user_id}}',
            '{{%polygen}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-polygen-user_id}}',
            '{{%polygen}}',
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
            '{{%fk-polygen-user_id}}',
            '{{%polygen}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-polygen-user_id}}',
            '{{%polygen}}'
        );

        $this->dropTable('{{%polygen}}');
    }
}
