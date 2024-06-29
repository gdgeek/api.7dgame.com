<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%score}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240629_102619_create_score_table extends Migration
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
        $this->createTable('{{%score}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'player_id' => $this->string()->notNull(),
            'score' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-score-verse_id}}',
            '{{%score}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-score-verse_id}}',
            '{{%score}}',
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
            '{{%fk-score-verse_id}}',
            '{{%score}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-score-verse_id}}',
            '{{%score}}'
        );

        $this->dropTable('{{%score}}');
    }
}
