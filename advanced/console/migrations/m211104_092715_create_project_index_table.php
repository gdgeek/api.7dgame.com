<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_index}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 * - `{{%project_data}}`
 */
class m211104_092715_create_project_index_table extends Migration
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
        $this->createTable('{{%project_index}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'author_id' => $this->integer()->notNull(),
            'image_id' => $this->integer(),
            'data_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ],$tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-project_index-author_id}}',
            '{{%project_index}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-project_index-author_id}}',
            '{{%project_index}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-project_index-image_id}}',
            '{{%project_index}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-project_index-image_id}}',
            '{{%project_index}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `data_id`
        $this->createIndex(
            '{{%idx-project_index-data_id}}',
            '{{%project_index}}',
            'data_id'
        );

        // add foreign key for table `{{%project_data}}`
        $this->addForeignKey(
            '{{%fk-project_index-data_id}}',
            '{{%project_index}}',
            'data_id',
            '{{%project_data}}',
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
            '{{%fk-project_index-author_id}}',
            '{{%project_index}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-project_index-author_id}}',
            '{{%project_index}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-project_index-image_id}}',
            '{{%project_index}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-project_index-image_id}}',
            '{{%project_index}}'
        );

        // drops foreign key for table `{{%project_data}}`
        $this->dropForeignKey(
            '{{%fk-project_index-data_id}}',
            '{{%project_index}}'
        );

        // drops index for column `data_id`
        $this->dropIndex(
            '{{%idx-project_index-data_id}}',
            '{{%project_index}}'
        );

        $this->dropTable('{{%project_index}}');
    }
}
