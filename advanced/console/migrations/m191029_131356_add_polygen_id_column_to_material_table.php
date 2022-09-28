<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 */
class m191029_131356_add_polygen_id_column_to_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'polygen_id', $this->integer()->notNull());

        // creates index for column `polygen_id`
        $this->createIndex(
            '{{%idx-material-polygen_id}}',
            '{{%material}}',
            'polygen_id'
        );

        // add foreign key for table `{{%polygen}}`
        $this->addForeignKey(
            '{{%fk-material-polygen_id}}',
            '{{%material}}',
            'polygen_id',
            '{{%polygen}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%polygen}}`
        $this->dropForeignKey(
            '{{%fk-material-polygen_id}}',
            '{{%material}}'
        );

        // drops index for column `polygen_id`
        $this->dropIndex(
            '{{%idx-material-polygen_id}}',
            '{{%material}}'
        );

        $this->dropColumn('{{%material}}', 'polygen_id');
    }
}
