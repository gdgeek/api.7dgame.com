<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%property}}`
 */
class m251223_071607_create_verse_property_table extends Migration
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
        $this->createTable('{{%verse_property}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'property_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_property-verse_id}}',
            '{{%verse_property}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_property-verse_id}}',
            '{{%verse_property}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-verse_property-property_id}}',
            '{{%verse_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-verse_property-property_id}}',
            '{{%verse_property}}',
            'property_id',
            '{{%property}}',
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
            '{{%fk-verse_property-verse_id}}',
            '{{%verse_property}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_property-verse_id}}',
            '{{%verse_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-verse_property-property_id}}',
            '{{%verse_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-verse_property-property_id}}',
            '{{%verse_property}}'
        );

        $this->dropTable('{{%verse_property}}');
    }
}
