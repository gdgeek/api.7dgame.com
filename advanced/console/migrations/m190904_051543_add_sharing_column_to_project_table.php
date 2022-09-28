<?php

use yii\db\Migration;

/**
 * Handles adding sharing to table `{{%project}}`.
 */
class m190904_051543_add_sharing_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'sharing', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'sharing');
    }
}
