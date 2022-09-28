<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%video}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m210521_163836_add_image_id_column_to_video_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%video}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-video-image_id}}',
            '{{%video}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-video-image_id}}',
            '{{%video}}',
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
            '{{%fk-video-image_id}}',
            '{{%video}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-video-image_id}}',
            '{{%video}}'
        );

        $this->dropColumn('{{%video}}', 'image_id');
    }
}
