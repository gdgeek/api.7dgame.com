<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%apple_id}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%vp_token}}`
 */
class m240809_150343_add_vp_token_id_column_to_apple_id_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%apple_id}}', 'vp_token_id', $this->integer());

        // creates index for column `vp_token_id`
        $this->createIndex(
            '{{%idx-apple_id-vp_token_id}}',
            '{{%apple_id}}',
            'vp_token_id'
        );

        // add foreign key for table `{{%vp_token}}`
        $this->addForeignKey(
            '{{%fk-apple_id-vp_token_id}}',
            '{{%apple_id}}',
            'vp_token_id',
            '{{%vp_token}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%vp_token}}`
        $this->dropForeignKey(
            '{{%fk-apple_id-vp_token_id}}',
            '{{%apple_id}}'
        );

        // drops index for column `vp_token_id`
        $this->dropIndex(
            '{{%idx-apple_id-vp_token_id}}',
            '{{%apple_id}}'
        );

        $this->dropColumn('{{%apple_id}}', 'vp_token_id');
    }
}
