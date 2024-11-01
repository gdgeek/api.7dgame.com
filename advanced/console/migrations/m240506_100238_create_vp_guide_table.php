<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vp_guide}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240506_100238_create_vp_guide_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%vp_guide}}', [
            'id' => $this->primaryKey(),
            'order' => $this->integer(),
            'level_id' => $this->integer()->notNull()->unique(),
        ], $tableOptions);

        // creates index for column `level_id`
        $this->createIndex(
            '{{%idx-vp_guide-level_id}}',
            '{{%vp_guide}}',
            'level_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-vp_guide-level_id}}',
            '{{%vp_guide}}',
            'level_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-vp_guide-level_id}}',
            '{{%vp_guide}}'
        );

        // drops index for column `level_id`
        $this->dropIndex(
            '{{%idx-vp_guide-level_id}}',
            '{{%vp_guide}}'
        );

        $this->dropTable('{{%vp_guide}}');
    }
}
