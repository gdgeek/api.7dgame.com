<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cyber_script}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%cyber}}`
 */
class m221111_171450_create_cyber_script_table extends Migration
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
        $this->createTable('{{%cyber_script}}', [
            'id' => $this->primaryKey(),
            'cyber_id' => $this->integer()->notNull(),
            'language' => $this->string()->notNull(),
            'script' => $this->text(),
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-cyber_script-cyber_id-language}}',
            '{{%cyber_script}}',
            'cyber_id,language',
            true
        );
        // creates index for column `cyber_id`
        $this->createIndex(
            '{{%idx-cyber_script-cyber_id}}',
            '{{%cyber_script}}',
            'cyber_id'
        );

        // add foreign key for table `{{%cyber}}`
        $this->addForeignKey(
            '{{%fk-cyber_script-cyber_id}}',
            '{{%cyber_script}}',
            'cyber_id',
            '{{%cyber}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%cyber}}`
        $this->dropForeignKey(
            '{{%fk-cyber_script-cyber_id}}',
            '{{%cyber_script}}'
        );

        // drops index for column `cyber_id`
        $this->dropIndex(
            '{{%idx-cyber_script-cyber_id-language}}',
            '{{%cyber_script}}'
        );

        $this->dropTable('{{%cyber_script}}');
    }
}
