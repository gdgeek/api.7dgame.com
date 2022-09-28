<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%material}}`.
 */
class m191021_142823_add_albedo_column_metallic_column_normal_column_occlusion_column_emission_column_to_material_table extends Migration
{
     /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%material}}', 'albedo', $this->integer());
        $this->addColumn('{{%material}}', 'metallic', $this->integer());
        $this->addColumn('{{%material}}', 'normal', $this->integer());
        $this->addColumn('{{%material}}', 'occlusion', $this->integer());
        $this->addColumn('{{%material}}', 'emission', $this->integer());

        // creates index for column `albedo`
        $this->createIndex(
            '{{%idx-material-albedo}}',
            '{{%material}}',
            'albedo'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-material-albedo}}',
            '{{%material}}',
            'albedo',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `metallic`
        $this->createIndex(
            '{{%idx-material-metallic}}',
            '{{%material}}',
            'metallic'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-material-metallic}}',
            '{{%material}}',
            'metallic',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `normal`
        $this->createIndex(
            '{{%idx-material-normal}}',
            '{{%material}}',
            'normal'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-material-normal}}',
            '{{%material}}',
            'normal',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `occlusion`
        $this->createIndex(
            '{{%idx-material-occlusion}}',
            '{{%material}}',
            'occlusion'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-material-occlusion}}',
            '{{%material}}',
            'occlusion',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `emission`
        $this->createIndex(
            '{{%idx-material-emission}}',
            '{{%material}}',
            'emission'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-material-emission}}',
            '{{%material}}',
            'emission',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}
