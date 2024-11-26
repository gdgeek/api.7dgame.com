<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%ai_rodin}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m241002_131644_drop_image_id_column_from_ai_rodin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-ai_rodin-image_id}}',
            '{{%ai_rodin}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-ai_rodin-image_id}}',
            '{{%ai_rodin}}'
        );

        $this->dropColumn('{{%ai_rodin}}', 'image_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%ai_rodin}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-ai_rodin-image_id}}',
            '{{%ai_rodin}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-ai_rodin-image_id}}',
            '{{%ai_rodin}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }
}
