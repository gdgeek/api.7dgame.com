<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m211207_232656_create_meta_table extends Migration
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
        $this->createTable('{{%meta}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'name' => $this->string()->notNull(),
            'verse_id' => $this->integer(),
            'info' => $this->json(),
            'image' => $this->integer(),
            'data' => $this->json(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-meta-verse_id}}',
            '{{%meta}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-meta-verse_id}}',
            '{{%meta}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );



        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-meta-author_id}}',
            '{{%meta}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-meta-author_id}}',
            '{{%meta}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-meta-updater_id}}',
            '{{%meta}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-meta-updater_id}}',
            '{{%meta}}',
            'updater_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image`
        $this->createIndex(
            '{{%idx-meta-image}}',
            '{{%meta}}',
            'image'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-meta-image}}',
            '{{%meta}}',
            'image',
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
            '{{%fk-meta-author_id}}',
            '{{%meta}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-meta-author_id}}',
            '{{%meta}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-meta-verse_id}}',
            '{{%meta}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-meta-verse_id}}',
            '{{%meta}}'
        );
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-meta-updater_id}}',
            '{{%meta}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-meta-updater_id}}',
            '{{%meta}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-meta-image}}',
            '{{%meta}}'
        );

        // drops index for column `image`
        $this->dropIndex(
            '{{%idx-meta-image}}',
            '{{%meta}}'
        );

        $this->dropTable('{{%meta}}');
    }
}
