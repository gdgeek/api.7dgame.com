<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%vp_token}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m240810_122437_drop_user_id_column_from_vp_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-vp_token-user_id}}',
            '{{%vp_token}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-vp_token-user_id}}',
            '{{%vp_token}}'
        );

        $this->dropColumn('{{%vp_token}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%vp_token}}', 'user_id', $this->integer());

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-vp_token-user_id}}',
            '{{%vp_token}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-vp_token-user_id}}',
            '{{%vp_token}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }
}
