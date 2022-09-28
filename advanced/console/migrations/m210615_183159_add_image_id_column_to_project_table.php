<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%project}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m210615_183159_add_image_id_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-project-image_id}}',
            '{{%project}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-project-image_id}}',
            '{{%project}}',
            'image_id',
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
            '{{%fk-project-image_id}}',
            '{{%project}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-project-image_id}}',
            '{{%project}}'
        );

        $this->dropColumn('{{%project}}', 'image_id');
    }
}
