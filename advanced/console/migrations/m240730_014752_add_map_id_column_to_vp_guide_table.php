<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vp_guide}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%vp_map}}`
 */
class m240730_014752_add_map_id_column_to_vp_guide_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vp_guide}}', 'map_id', $this->integer());

        // creates index for column `map_id`
        $this->createIndex(
            '{{%idx-vp_guide-map_id}}',
            '{{%vp_guide}}',
            'map_id'
        );

        // add foreign key for table `{{%vp_map}}`
        $this->addForeignKey(
            '{{%fk-vp_guide-map_id}}',
            '{{%vp_guide}}',
            'map_id',
            '{{%vp_map}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%vp_map}}`
        $this->dropForeignKey(
            '{{%fk-vp_guide-map_id}}',
            '{{%vp_guide}}'
        );

        // drops index for column `map_id`
        $this->dropIndex(
            '{{%idx-vp_guide-map_id}}',
            '{{%vp_guide}}'
        );

        $this->dropColumn('{{%vp_guide}}', 'map_id');
    }
}
