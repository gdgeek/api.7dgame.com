<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%multilanguage_verse}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240627_050132_create_multilanguage_verse_table extends Migration
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
        $this->createTable('{{%multilanguage_verse}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'language' => $this->string(256)->notNull(),
            'name' => $this->string(),
            'description' => $this->string(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-multilanguage_verse-verse_id}}',
            '{{%multilanguage_verse}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-multilanguage_verse-verse_id}}',
            '{{%multilanguage_verse}}',
            'verse_id',
            '{{%verse}}',
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
            '{{%fk-multilanguage_verse-verse_id}}',
            '{{%multilanguage_verse}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-multilanguage_verse-verse_id}}',
            '{{%multilanguage_verse}}'
        );

        $this->dropTable('{{%multilanguage_verse}}');
    }
}
