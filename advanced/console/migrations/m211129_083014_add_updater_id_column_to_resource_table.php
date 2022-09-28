<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%resource}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m211129_083014_add_updater_id_column_to_resource_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%resource}}', 'updater_id', $this->integer());

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-resource-updater_id}}',
            '{{%resource}}',
            'updater_id'
        );

        // add foreign key for table `{{%updater}}`
        $this->addForeignKey(
            '{{%fk-resource-updater_id}}',
            '{{%resource}}',
            'updater_id',
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
        // drops foreign key for table `{{%updater}}`
        $this->dropForeignKey(
            '{{%fk-resource-updater_id}}',
            '{{%resource}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-resource-updater_id}}',
            '{{%resource}}'
        );

        $this->dropColumn('{{%resource}}', 'updater_id');
    }
}
