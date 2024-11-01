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
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }
            $this->createTable('{{%verse_release}}', [
                'id' => $this->primaryKey(),
                'code' => $this->string()->notNull()->unique(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp(),
                'lifetime' => $this->integer()->notNull()->defaultValue(86400),
                'verse_id' => $this->integer()->notNull()->unique(),
            ], $tableOptions);
            
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
    