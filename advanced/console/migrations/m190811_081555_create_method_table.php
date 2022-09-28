<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%method}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m190811_081555_create_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%method}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'definition' => $this->text(),
            'generator' => $this->text(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-method-user_id}}',
            '{{%method}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-method-user_id}}',
            '{{%method}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-method-user_id}}',
            '{{%method}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-method-user_id}}',
            '{{%method}}'
        );

        $this->dropTable('{{%method}}');
    }
}
