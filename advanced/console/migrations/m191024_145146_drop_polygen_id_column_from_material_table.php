<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%material}}`.
 */
class m191024_145146_drop_polygen_id_column_from_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

		$this->addColumn('{{%material}}', 'polygen_id', $this->integer()->notNull()->unique());

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
}
