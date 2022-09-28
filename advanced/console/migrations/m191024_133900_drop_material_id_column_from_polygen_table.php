<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%polygen}}`.
 */
class m191024_133900_drop_material_id_column_from_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
