<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rete}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m211213_232656_create_verse_rete_table extends Migration
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
        $this->createTable('{{%verse_rete}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'verse_id' => $this->integer(),
            'data' => $this->json(),
        ], $tableOptions);

       // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_rete-verse_id}}',
            '{{%verse_rete}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_rete-verse_id}}',
            '{{%verse_rete}}',
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
       
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse_rete-verse_id}}',
            '{{%verse_rete}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-verse_rete-verse_id}}',
            '{{%verse_rete}}'
        );


        $this->dropTable('{{%verse_rete}}');
    }
}
