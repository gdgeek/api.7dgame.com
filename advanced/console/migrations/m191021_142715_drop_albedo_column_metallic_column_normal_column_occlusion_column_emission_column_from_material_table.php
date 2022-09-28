<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%material}}`.
 */
class m191021_142715_drop_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_from_material_table extends Migration
{
      /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	
	
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-material-albedo}}',
            '{{%material}}'
        );

        // drops index for column `albedo`
        $this->dropIndex(
            '{{%idx-material-albedo}}',
            '{{%material}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-material-metallic}}',
            '{{%material}}'
        );

        // drops index for column `metallic`
        $this->dropIndex(
            '{{%idx-material-metallic}}',
            '{{%material}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-material-normal}}',
            '{{%material}}'
        );

        // drops index for column `normal`
        $this->dropIndex(
            '{{%idx-material-normal}}',
            '{{%material}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-material-occlusion}}',
            '{{%material}}'
        );

        // drops index for column `occlusion`
        $this->dropIndex(
            '{{%idx-material-occlusion}}',
            '{{%material}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-material-emission}}',
            '{{%material}}'
        );

        // drops index for column `emission`
        $this->dropIndex(
            '{{%idx-material-emission}}',
            '{{%material}}'
        );
		

        $this->dropColumn('{{%material}}', 'albedo');
        $this->dropColumn('{{%material}}', 'metallic');
        $this->dropColumn('{{%material}}', 'normal');
        $this->dropColumn('{{%material}}', 'occlusion');
        $this->dropColumn('{{%material}}', 'emission');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%material}}', 'albedo', $this->string());
        $this->addColumn('{{%material}}', 'metallic', $this->string());
        $this->addColumn('{{%material}}', 'normal', $this->string());
        $this->addColumn('{{%material}}', 'occlusion', $this->string());
        $this->addColumn('{{%material}}', 'emission', $this->string());
    }
}
