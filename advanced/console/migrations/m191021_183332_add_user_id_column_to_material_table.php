<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m191021_183332_add_user_id_column_to_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'user_id', $this->integer()->notNull());

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-material-user_id}}',
            '{{%material}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-material-user_id}}',
            '{{%material}}',
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
            '{{%fk-material-user_id}}',
            '{{%material}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-material-user_id}}',
            '{{%material}}'
        );

        $this->dropColumn('{{%material}}', 'user_id');
    }
}
