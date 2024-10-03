<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ai_rodin}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%resource}}`
 */
class m241002_131737_add_resource_id_column_to_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ai_rodin}}', 'resource_id', $this->integer());

        // creates index for column `resource_id`
        $this->createIndex(
            '{{%idx-ai_rodin-resource_id}}',
            '{{%ai_rodin}}',
            'resource_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-ai_rodin-resource_id}}',
            '{{%ai_rodin}}',
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
            '{{%fk-ai_rodin-resource_id}}',
            '{{%ai_rodin}}'
        );

        // drops index for column `resource_id`
        $this->dropIndex(
            '{{%idx-ai_rodin-resource_id}}',
            '{{%ai_rodin}}'
        );

        $this->dropColumn('{{%ai_rodin}}', 'resource_id');
    }
}
