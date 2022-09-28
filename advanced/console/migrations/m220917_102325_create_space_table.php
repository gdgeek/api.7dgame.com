<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%space}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 * - `{{%file}}`
 * - `{{%file}}`
 */
class m220917_102325_create_space_table extends Migration
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
        $this->createTable('{{%space}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'sample_id' => $this->integer()->notNull(),
            'mesh_id' => $this->integer()->notNull(),
            'dat_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-space-author_id}}',
            '{{%space}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-space-author_id}}',
            '{{%space}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `sample_id`
        $this->createIndex(
            '{{%idx-space-sample_id}}',
            '{{%space}}',
            'sample_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-space-sample_id}}',
            '{{%space}}',
            'sample_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `mesh_id`
        $this->createIndex(
            '{{%idx-space-mesh_id}}',
            '{{%space}}',
            'mesh_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-space-mesh_id}}',
            '{{%space}}',
            'mesh_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `dat_id`
        $this->createIndex(
            '{{%idx-space-dat_id}}',
            '{{%space}}',
            'dat_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-space-dat_id}}',
            '{{%space}}',
            'dat_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-space-image_id}}',
            '{{%space}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-space-image_id}}',
            '{{%space}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-space-author_id}}',
            '{{%space}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-space-author_id}}',
            '{{%space}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-space-sample_id}}',
            '{{%space}}'
        );

        // drops index for column `sample_id`
        $this->dropIndex(
            '{{%idx-space-sample_id}}',
            '{{%space}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-space-mesh_id}}',
            '{{%space}}'
        );

        // drops index for column `mesh_id`
        $this->dropIndex(
            '{{%idx-space-mesh_id}}',
            '{{%space}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-space-dat_id}}',
            '{{%space}}'
        );

        // drops index for column `dat_id`
        $this->dropIndex(
            '{{%idx-space-dat_id}}',
            '{{%space}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-space-image_id}}',
            '{{%space}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-space-image_id}}',
            '{{%space}}'
        );

        $this->dropTable('{{%space}}');

    }
}
