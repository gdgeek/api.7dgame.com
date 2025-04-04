<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%snapshot}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250404_084503_drop_name_column_description_column_image_column_author_id_column_type_column_from_snapshot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-snapshot-author_id}}',
            '{{%snapshot}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-snapshot-author_id}}',
            '{{%snapshot}}'
        );

        $this->dropColumn('{{%snapshot}}', 'name');
        $this->dropColumn('{{%snapshot}}', 'description');
        $this->dropColumn('{{%snapshot}}', 'image');
        $this->dropColumn('{{%snapshot}}', 'author_id');
        $this->dropColumn('{{%snapshot}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%snapshot}}', 'name', $this->string());
        $this->addColumn('{{%snapshot}}', 'description', $this->string());
        $this->addColumn('{{%snapshot}}', 'image', $this->json());
        $this->addColumn('{{%snapshot}}', 'author_id', $this->integer());
        $this->addColumn('{{%snapshot}}', 'type', $this->string());

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-snapshot-author_id}}',
            '{{%snapshot}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-snapshot-author_id}}',
            '{{%snapshot}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }
}
