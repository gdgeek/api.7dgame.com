<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%apple_id}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%vp_token}}`
 */
class m240918_132316_drop_vp_token_id_column_from_apple_id_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
