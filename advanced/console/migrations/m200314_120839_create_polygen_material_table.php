<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%polygen_material}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%polygen}}`
 * - `{{%material}}`
 */
class m200314_120839_create_polygen_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%polygen_material}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(),
            'polygen_id' => $this->integer(),
            'material_id' => $this->integer(),
        ],$tableOptions);

        // creates index for column `polygen_id`
        $this->createIndex(
            '{{%idx-polygen_material-polygen_id}}',
            '{{%polygen_material}}',
            'polygen_id'
        );

        // add foreign key for table `{{%polygen}}`
        $this->addForeignKey(
            '{{%fk-polygen_material-polygen_id}}',
            '{{%polygen_material}}',
            'polygen_id',
            '{{%polygen}}',
            'id',
            'CASCADE'
        );

        // creates index for column `material_id`
        $this->createIndex(
            '{{%idx-polygen_material-material_id}}',
            '{{%polygen_material}}',
            'material_id'
        );

        // add foreign key for table `{{%material}}`
        $this->addForeignKey(
            '{{%fk-polygen_material-material_id}}',
            '{{%polygen_material}}',
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
        // drops foreign key for table `{{%polygen}}`
        $this->dropForeignKey(
            '{{%fk-polygen_material-polygen_id}}',
            '{{%polygen_material}}'
        );

        // drops index for column `polygen_id`
        $this->dropIndex(
            '{{%idx-polygen_material-polygen_id}}',
            '{{%polygen_material}}'
        );

        // drops foreign key for table `{{%material}}`
        $this->dropForeignKey(
            '{{%fk-polygen_material-material_id}}',
            '{{%polygen_material}}'
        );

        // drops index for column `material_id`
        $this->dropIndex(
            '{{%idx-polygen_material-material_id}}',
            '{{%polygen_material}}'
        );

        $this->dropTable('{{%polygen_material}}');
    }
}
