<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%snapshot}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%user}}`
 * - `{{%user}}`
 */
class m250330_134002_create_snapshot_table extends Migration
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
        $this->createTable('{{%snapshot}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer(),
            'snapshot' => $this->json()->notNull(),
            'created_at' => $this->dateTime(),
            'author_id' => $this->integer(),
            'created_by' => $this->integer(),
            'type' => $this->string(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-snapshot-verse_id}}',
            '{{%snapshot}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-snapshot-verse_id}}',
            '{{%snapshot}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-snapshot-author_id}}',
            '{{%snapshot}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-snapshot-author_id}}',
            '{{%snapshot}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `created_by`
        $this->createIndex(
            '{{%idx-snapshot-created_by}}',
            '{{%snapshot}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-snapshot-created_by}}',
            '{{%snapshot}}',
            'created_by',
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
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-snapshot-verse_id}}',
            '{{%snapshot}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-snapshot-verse_id}}',
            '{{%snapshot}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-snapshot-author_id}}',
            '{{%snapshot}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-snapshot-author_id}}',
            '{{%snapshot}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-snapshot-created_by}}',
            '{{%snapshot}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-snapshot-created_by}}',
            '{{%snapshot}}'
        );

        $this->dropTable('{{%snapshot}}');
    }
}
