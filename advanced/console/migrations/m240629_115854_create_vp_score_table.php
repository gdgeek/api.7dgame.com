<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vp_score}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240629_115854_create_vp_score_table extends Migration
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

        $this->createTable('{{%vp_score}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'player_id' => $this->string(),
            'score' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-vp_score-verse_id}}',
            '{{%vp_score}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-vp_score-verse_id}}',
            '{{%vp_score}}',
            'verse_id',
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
            '{{%fk-vp_score-verse_id}}',
            '{{%vp_score}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-vp_score-verse_id}}',
            '{{%vp_score}}'
        );

        $this->dropTable('{{%vp_score}}');
    }
}
