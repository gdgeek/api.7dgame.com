<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%polygen}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%material}}`
 */
class m191018_083804_add_material_id_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'material_id', $this->integer());

        // creates index for column `material_id`
        $this->createIndex(
            '{{%idx-polygen-material_id}}',
            '{{%polygen}}',
            'material_id'
        );

        // add foreign key for table `{{%material}}`
        $this->addForeignKey(
            '{{%fk-polygen-material_id}}',
            '{{%polygen}}',
            'material_id',
            '{{%material}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%material}}`
        $this->dropForeignKey(
            '{{%fk-polygen-material_id}}',
            '{{%polygen}}'
        );

        // drops index for column `material_id`
        $this->dropIndex(
            '{{%idx-polygen-material_id}}',
            '{{%polygen}}'
        );

        $this->dropColumn('{{%polygen}}', 'material_id');
    }
}
