<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta_version}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%version}}`
 */
class m251223_094555_create_meta_version_table extends Migration
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
        $this->createTable('{{%meta_version}}', [
            'id' => $this->primaryKey(),
            'meta_id' => $this->integer()->notNull(),
            'version_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_version-meta_id}}',
            '{{%meta_version}}',
            'meta_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-meta_version-meta_id}}',
            '{{%meta_version}}',
            'meta_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `version_id`
        $this->createIndex(
            '{{%idx-meta_version-version_id}}',
            '{{%meta_version}}',
            'version_id'
        );

        // add foreign key for table `{{%version}}`
        $this->addForeignKey(
            '{{%fk-meta_version-version_id}}',
            '{{%meta_version}}',
            'version_id',
            '{{%version}}',
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
            '{{%fk-meta_version-meta_id}}',
            '{{%meta_version}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-meta_version-meta_id}}',
            '{{%meta_version}}'
        );

        // drops foreign key for table `{{%version}}`
        $this->dropForeignKey(
            '{{%fk-meta_version-version_id}}',
            '{{%meta_version}}'
        );

        // drops index for column `version_id`
        $this->dropIndex(
            '{{%idx-meta_version-version_id}}',
            '{{%meta_version}}'
        );

        $this->dropTable('{{%meta_version}}');
    }
}
