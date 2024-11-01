<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vp_level}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%vp_token}}`
 * - `{{%vp_guide}}`
 */
class m240725_043603_create_vp_level_table extends Migration
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

        $this->createTable('{{%vp_level}}', [
            'id' => $this->primaryKey(),
            'player_id' => $this->integer()->notNull(),
            'guide_id' => $this->integer()->notNull(),
            'record' => $this->float(),
            'score' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
        ], $tableOptions);

        // creates index for column `player_id`
        $this->createIndex(
            '{{%idx-vp_level-player_id}}',
            '{{%vp_level}}',
            'player_id'
        );

        // add foreign key for table `{{%vp_token}}`
        $this->addForeignKey(
            '{{%fk-vp_level-player_id}}',
            '{{%vp_level}}',
            'player_id',
            '{{%vp_token}}',
            'id',
            'CASCADE'
        );

        // creates index for column `guide_id`
        $this->createIndex(
            '{{%idx-vp_level-guide_id}}',
            '{{%vp_level}}',
            'guide_id'
        );

        // add foreign key for table `{{%vp_guide}}`
        $this->addForeignKey(
            '{{%fk-vp_level-guide_id}}',
            '{{%vp_level}}',
            'guide_id',
            '{{%vp_guide}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%vp_token}}`
        $this->dropForeignKey(
            '{{%fk-vp_level-player_id}}',
            '{{%vp_level}}'
        );

        // drops index for column `player_id`
        $this->dropIndex(
            '{{%idx-vp_level-player_id}}',
            '{{%vp_level}}'
        );

        // drops foreign key for table `{{%vp_guide}}`
        $this->dropForeignKey(
            '{{%fk-vp_level-guide_id}}',
            '{{%vp_level}}'
        );

        // drops index for column `guide_id`
        $this->dropIndex(
            '{{%idx-vp_level-guide_id}}',
            '{{%vp_level}}'
        );

        $this->dropTable('{{%vp_level}}');
    }
}
