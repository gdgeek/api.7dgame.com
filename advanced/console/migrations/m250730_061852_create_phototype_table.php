<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%phototype}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 * - `{{%user}}`
 */
class m250730_061852_create_phototype_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%phototype}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'data' => $this->json(),
            'schema' => $this->json(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'uuid' => $this->string()->unique(),
            'image_id' => $this->integer(),
            'updater_id' => $this->integer(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-phototype-author_id}}',
            '{{%phototype}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-phototype-author_id}}',
            '{{%phototype}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-phototype-image_id}}',
            '{{%phototype}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-phototype-image_id}}',
            '{{%phototype}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-phototype-updater_id}}',
            '{{%phototype}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-phototype-updater_id}}',
            '{{%phototype}}',
            'updater_id',
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
            '{{%fk-phototype-author_id}}',
            '{{%phototype}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-phototype-author_id}}',
            '{{%phototype}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-phototype-image_id}}',
            '{{%phototype}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-phototype-image_id}}',
            '{{%phototype}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-phototype-updater_id}}',
            '{{%phototype}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-phototype-updater_id}}',
            '{{%phototype}}'
        );

        $this->dropTable('{{%phototype}}');
    }
}
