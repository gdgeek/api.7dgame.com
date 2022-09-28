<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%file_reference}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m191021_090421_create_file_reference_table extends Migration
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
        $this->createTable('{{%file_reference}}', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'type' => $this->string(),
        ], $tableOptions);

        // creates index for column `file_id`
        $this->createIndex(
            '{{%idx-file_reference-file_id}}',
            '{{%file_reference}}',
            'file_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-file_reference-file_id}}',
            '{{%file_reference}}',
            'file_id',
            '{{%file}}',
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
            '{{%fk-file_reference-file_id}}',
            '{{%file_reference}}'
        );

        // drops index for column `file_id`
        $this->dropIndex(
            '{{%idx-file_reference-file_id}}',
            '{{%file_reference}}'
        );

        $this->dropTable('{{%file_reference}}');
    }
}
