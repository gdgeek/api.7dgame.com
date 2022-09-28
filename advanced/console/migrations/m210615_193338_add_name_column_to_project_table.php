<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%project}}`.
 */
class m210615_193338_add_name_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'name', $this->string());
          // creates index for column `name`
          $this->createIndex(
            '{{%idx-project-name}}',
            '{{%project}}',
            'name'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         // drops index for column `name`
         $this->dropIndex(
            '{{%idx-project-name}}',
            '{{%project}}'
        );
        $this->dropColumn('{{%project}}', 'name');
    }
}
