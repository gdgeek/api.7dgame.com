<?php

use yii\db\Migration;
/**
 * Handles the creation of table `{{%resource}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 * - `{{%file}}`
 */
class m211102_062127_create_resource_table extends Migration
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
        $this->createTable('{{%resource}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'file_id' => $this->integer()->notNull(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
        ], $tableOptions);
        // creates index for column `type`
        $this->createIndex(
            '{{%idx-resource-type}}',
            '{{%resource}}',
            'type'
        );


        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-resource-author_id}}',
            '{{%resource}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-resource-author_id}}',
            '{{%resource}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `file_id`
        $this->createIndex(
            '{{%idx-resource-file_id}}',
            '{{%resource}}',
            'file_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-resource-file_id}}',
            '{{%resource}}',
            'file_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-resource-image_id}}',
            '{{%resource}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-resource-image_id}}',
            '{{%resource}}',
            'image_id',
            '{{%file}}',
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
            '{{%fk-resource-author_id}}',
            '{{%resource}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-resource-author_id}}',
            '{{%resource}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-resource-file_id}}',
            '{{%resource}}'
        );

        // drops index for column `file_id`
        $this->dropIndex(
            '{{%idx-resource-file_id}}',
            '{{%resource}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-resource-image_id}}',
            '{{%resource}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-resource-image_id}}',
            '{{%resource}}'
        );

         // drops index for column `type`
         $this->dropIndex(
            '{{%idx-resource-type}}',
            '{{%resource}}',
            'type'
        );
        $this->dropTable('{{%resource}}');
    }
}
