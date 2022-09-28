<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m220316_092100_add_avatar_id_column_to_user_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_info}}', 'avatar_id', $this->integer());

        // creates index for column `avatar_id`
        $this->createIndex(
            '{{%idx-user_info-avatar_id}}',
            '{{%user_info}}',
            'avatar_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-user_info-avatar_id}}',
            '{{%user_info}}',
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
            '{{%fk-user_info-avatar_id}}',
            '{{%user_info}}'
        );

        // drops index for column `avatar_id`
        $this->dropIndex(
            '{{%idx-user_info-avatar_id}}',
            '{{%user_info}}'
        );

        $this->dropColumn('{{%user_info}}', 'avatar_id');
    }
}
