<?php

use yii\db\Migration;

/**
* Handles the creation of table `{{%verse_release}}`.
* Has foreign keys to the tables:
    *
    * - `{{%verse}}`
    */
    class m240922_151125_create_verse_release_table extends Migration
    {
        /**
        * {@inheritdoc}
        */
        public function safeUp()
        {
            $this->createTable('{{%verse_release}}', [
                'id' => $this->primaryKey(),
                'code' => $this->string()->notNull()->unique(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp(),
                'lifetime' => $this->integer()->notNull()->defaultValue(86400),
                'verse_id' => $this->integer()->notNull()->unique(),
            ]);
            
            // creates index for column `verse_id`
            $this->createIndex(
                '{{%idx-verse_release-verse_id}}',
                '{{%verse_release}}',
                'verse_id'
            );
            
            // add foreign key for table `{{%verse}}`
            $this->addForeignKey(
                '{{%fk-verse_release-verse_id}}',
                '{{%verse_release}}',
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
                '{{%fk-verse_release-verse_id}}',
                '{{%verse_release}}'
            );
            
            // drops index for column `verse_id`
            $this->dropIndex(
                '{{%idx-verse_release-verse_id}}',
                '{{%verse_release}}'
            );
            
            $this->dropTable('{{%verse_release}}');
        }
    }
    