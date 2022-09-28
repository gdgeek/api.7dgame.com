<?php

use yii\db\Migration;

/**
 * Handles adding used_column_auth_item_name to table `{{%invitation}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%auth_item}}`
 */
class m190526_145637_add_used_column_auth_item_name_column_to_invitation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%invitation}}', 'used', $this->boolean());
        $this->addColumn('{{%invitation}}', 'auth_item_name', $this->string());

        // creates index for column `auth_item_name`
        $this->createIndex(
            '{{%idx-invitation-auth_item_name}}',
            '{{%invitation}}',
            'auth_item_name'
        );

        // add foreign key for table `{{%auth_item}}`
        $this->addForeignKey(
            '{{%fk-invitation-auth_item_name}}',
            '{{%invitation}}',
            'auth_item_name',
            '{{%auth_item}}',
            'name',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%auth_item}}`
        $this->dropForeignKey(
            '{{%fk-invitation-auth_item_name}}',
            '{{%invitation}}'
        );

        // drops index for column `auth_item_name`
        $this->dropIndex(
            '{{%idx-invitation-auth_item_name}}',
            '{{%invitation}}'
        );

        $this->dropColumn('{{%invitation}}', 'used');
        $this->dropColumn('{{%invitation}}', 'auth_item_name');
    }
}
