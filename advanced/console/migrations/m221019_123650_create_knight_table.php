<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%resource}}`
 * - `{{%resource}}`
 */
class m221019_123650_create_knight_table extends Migration
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
        $this->createTable('{{%knight}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'create_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp(),
            'info' => $this->json(),
            'data' => $this->json(),
            'image_id' => $this->integer(),
            'mesh_id' => $this->integer(),
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-knight-author_id}}',
            '{{%knight}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-knight-author_id}}',
            '{{%knight}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-knight-updater_id}}',
            '{{%knight}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-knight-updater_id}}',
            '{{%knight}}',
            'updater_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-knight-image_id}}',
            '{{%knight}}',
            'image_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-knight-image_id}}',
            '{{%knight}}',
            'image_id',
            '{{%resource}}',
            'id',
            'CASCADE'
        );

        // creates index for column `mesh_id`
        $this->createIndex(
            '{{%idx-knight-mesh_id}}',
            '{{%knight}}',
            'mesh_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-knight-mesh_id}}',
            '{{%knight}}',
            'mesh_id',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-knight-author_id}}',
            '{{%knight}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-knight-author_id}}',
            '{{%knight}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-knight-updater_id}}',
            '{{%knight}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-knight-updater_id}}',
            '{{%knight}}'
        );

        // drops foreign key for table `{{%resource}}`
        $this->dropForeignKey(
            '{{%fk-knight-image_id}}',
            '{{%knight}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-knight-image_id}}',
            '{{%knight}}'
        );

        // drops foreign key for table `{{%resource}}`
        $this->dropForeignKey(
            '{{%fk-knight-mesh_id}}',
            '{{%knight}}'
        );

        // drops index for column `mesh_id`
        $this->dropIndex(
            '{{%idx-knight-mesh_id}}',
            '{{%knight}}'
        );

        $this->dropTable('{{%knight}}');
    }
}
