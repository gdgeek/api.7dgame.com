<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse}}`.
 */
class m220930_092422_add_version_column_to_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse}}', 'version', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%verse}}', 'version');
    }
}
