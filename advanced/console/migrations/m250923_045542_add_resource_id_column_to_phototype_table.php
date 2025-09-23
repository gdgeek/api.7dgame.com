<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%phototype}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%resource}}`
 */
class m250923_045542_add_resource_id_column_to_phototype_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%phototype}}', 'resource_id', $this->integer());

        // creates index for column `resource_id`
        $this->createIndex(
            '{{%idx-phototype-resource_id}}',
            '{{%phototype}}',
            'resource_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-phototype-resource_id}}',
            '{{%phototype}}',
            'resource_id',
            '{{%resource}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%resource}}`
        $this->dropForeignKey(
            '{{%fk-phototype-resource_id}}',
            '{{%phototype}}'
        );

        // drops index for column `resource_id`
        $this->dropIndex(
            '{{%idx-phototype-resource_id}}',
            '{{%phototype}}'
        );

        $this->dropColumn('{{%phototype}}', 'resource_id');
    }
}
