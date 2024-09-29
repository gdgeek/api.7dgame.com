<?php

use yii\db\Migration;

/**
* Handles the creation of table `{{%ai_rodin}}`.
* Has foreign keys to the tables:
    *
    * - `{{%file}}`
    * - `{{%user}}`
    */
    class m240928_072721_create_ai_rodin_table extends Migration
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
            $this->createTable('{{%ai_rodin}}', [
                'id' => $this->primaryKey(),
                'token' => $this->string()->notNull()->unique(),
                'prompt' => $this->string(),
                'image_id' => $this->integer(),
                'status' => $this->json(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'user_id' => $this->integer()->notNull(),
            ], $tableOptions);
            
            // creates index for column `image_id`
            $this->createIndex(
                '{{%idx-ai_rodin-image_id}}',
                '{{%ai_rodin}}',
                'image_id'
            );
            
            // add foreign key for table `{{%file}}`
            $this->addForeignKey(
                '{{%fk-ai_rodin-image_id}}',
                '{{%ai_rodin}}',
                'image_id',
                '{{%file}}',
                'id',
                'CASCADE'
            );
            
            // creates index for column `user_id`
            $this->createIndex(
                '{{%idx-ai_rodin-user_id}}',
                '{{%ai_rodin}}',
                'user_id'
            );
            
            // add foreign key for table `{{%user}}`
            $this->addForeignKey(
                '{{%fk-ai_rodin-user_id}}',
                '{{%ai_rodin}}',
                'user_id',
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
            // drops foreign key for table `{{%file}}`
            $this->dropForeignKey(
                '{{%fk-ai_rodin-image_id}}',
                '{{%ai_rodin}}'
            );
            
            // drops index for column `image_id`
            $this->dropIndex(
                '{{%idx-ai_rodin-image_id}}',
                '{{%ai_rodin}}'
            );
            
            // drops foreign key for table `{{%user}}`
            $this->dropForeignKey(
                '{{%fk-ai_rodin-user_id}}',
                '{{%ai_rodin}}'
            );
            
            // drops index for column `user_id`
            $this->dropIndex(
                '{{%idx-ai_rodin-user_id}}',
                '{{%ai_rodin}}'
            );
            
            $this->dropTable('{{%ai_rodin}}');
        }
    }
    