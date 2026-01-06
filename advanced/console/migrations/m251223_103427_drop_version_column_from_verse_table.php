<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%verse}}`.
 */
class m251223_103427_drop_version_column_from_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%verse}}', 'version');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%verse}}', 'version', $this->integer()->defaultValue(1));
    }
}
