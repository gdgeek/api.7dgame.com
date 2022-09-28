<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%content_type}}`
 */
class m191120_074843_create_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%content}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'type' => $this->integer(),
            'picture' => $this->string(),
            'video' => $this->string(),
            'text' => $this->text(),
            'blog' => $this->string(),
            'created_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `type`
        $this->createIndex(
            '{{%idx-content-type}}',
            '{{%content}}',
            'type'
        );

        // add foreign key for table `{{%content_type}}`
        $this->addForeignKey(
            '{{%fk-content-type}}',
            '{{%content}}',
            'type',
            '{{%content_type}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%content_type}}`
        $this->dropForeignKey(
            '{{%fk-content-type}}',
            '{{%content}}'
        );

        // drops index for column `type`
        $this->dropIndex(
            '{{%idx-content-type}}',
            '{{%content}}'
        );

        $this->dropTable('{{%content}}');
    }
}
