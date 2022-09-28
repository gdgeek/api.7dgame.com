<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%user_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m220316_092238_drop_avatar_column_from_user_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-user_info-avatar}}',
            '{{%user_info}}'
        );

        // drops index for column `avatar`
        $this->dropIndex(
            '{{%idx-user_info-avatar}}',
            '{{%user_info}}'
        );

        $this->dropColumn('{{%user_info}}', 'avatar');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user_info}}', 'avatar', $this->integer());

        // creates index for column `avatar`
        $this->createIndex(
            '{{%idx-user_info-avatar}}',
            '{{%user_info}}',
            'avatar'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-user_info-avatar}}',
            '{{%user_info}}',
            'avatar',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }
}
