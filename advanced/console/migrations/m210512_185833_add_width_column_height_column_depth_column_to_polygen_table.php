<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%polygen}}`.
 */
class m210512_185833_add_width_column_height_column_depth_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'width', $this->float());
        $this->addColumn('{{%polygen}}', 'height', $this->float());
        $this->addColumn('{{%polygen}}', 'depth', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%polygen}}', 'width');
        $this->dropColumn('{{%polygen}}', 'height');
        $this->dropColumn('{{%polygen}}', 'depth');
    }
}
