<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m211227_082518_add_avatar_id_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
