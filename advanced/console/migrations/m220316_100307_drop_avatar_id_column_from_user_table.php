<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%user}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m220316_100307_drop_avatar_id_column_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-user-avatar_id}}',
            '{{%user}}'
        );

        // drops index for column `avatar_id`
        $this->dropIndex(
            '{{%idx-user-avatar_id}}',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'avatar_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'avatar_id', $this->integer());

        // creates index for column `avatar_id`
        $this->createIndex(
            '{{%idx-user-avatar_id}}',
            '{{%user}}',
            'avatar_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-user-avatar_id}}',
            '{{%user}}',
            'avatar_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }
}
