<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse}}`.
 */
class m240428_151401_add_uuid_column_to_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse}}', 'uuid', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse}}', 'uuid');
    }
}
