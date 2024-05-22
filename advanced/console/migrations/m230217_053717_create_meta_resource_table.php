<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta_resource}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%meta}}`
 * - `{{%resource}}`
 */
class m230217_053717_create_meta_resource_table extends Migration
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
        $this->createTable('{{%meta_resource}}', [
            'id' => $this->primaryKey(),
            'meta_id' => $this->integer()->notNull(),
            'resource_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_resource-meta_id}}',
            '{{%meta_resource}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-meta_resource-meta_id}}',
            '{{%meta_resource}}',
            'meta_id',
            '{{%meta}}',
            'id',
            'CASCADE'
        );

        // creates index for column `resource_id`
        $this->createIndex(
            '{{%idx-meta_resource-resource_id}}',
            '{{%meta_resource}}',
            'resource_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-meta_resource-resource_id}}',
            '{{%meta_resource}}',
            'resource_id',
            '{{%resource}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%meta}}`
        $this->dropForeignKey(
            '{{%fk-meta_resource-meta_id}}',
            '{{%meta_resource}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-meta_resource-meta_id}}',
            '{{%meta_resource}}'
        );

        // drops foreign key for table `{{%resource}}`
        $this->dropForeignKey(
            '{{%fk-meta_resource-resource_id}}',
            '{{%meta_resource}}'
        );

        // drops index for column `resource_id`
        $this->dropIndex(
            '{{%idx-meta_resource-resource_id}}',
            '{{%meta_resource}}'
        );

        $this->dropTable('{{%meta_resource}}');
    }
}
