<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_version}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%version}}`
 */
class m251223_094544_create_verse_version_table extends Migration
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
        $this->createTable('{{%verse_version}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'version_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_version-verse_id}}',
            '{{%verse_version}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_version-verse_id}}',
            '{{%verse_version}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `version_id`
        $this->createIndex(
            '{{%idx-verse_version-version_id}}',
            '{{%verse_version}}',
            'version_id'
        );

        // add foreign key for table `{{%version}}`
        $this->addForeignKey(
            '{{%fk-verse_version-version_id}}',
            '{{%verse_version}}',
            'version_id',
            '{{%version}}',
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
            '{{%fk-verse_version-verse_id}}',
            '{{%verse_version}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_version-verse_id}}',
            '{{%verse_version}}'
        );

        // drops foreign key for table `{{%version}}`
        $this->dropForeignKey(
            '{{%fk-verse_version-version_id}}',
            '{{%verse_version}}'
        );

        // drops index for column `version_id`
        $this->dropIndex(
            '{{%idx-verse_version-version_id}}',
            '{{%verse_version}}'
        );

        $this->dropTable('{{%verse_version}}');
    }
}
