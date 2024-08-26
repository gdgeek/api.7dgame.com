<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta_code}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%meta}}`
 * - `{{%code}}`
 */
class m240826_060729_create_meta_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meta_code}}', [
            'id' => $this->primaryKey(),
            'meta_id' => $this->integer()->notNull()->unique(),
            'code_id' => $this->integer()->unique(),
        ]);

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_code-meta_id}}',
            '{{%meta_code}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-meta_code-meta_id}}',
            '{{%meta_code}}',
            'meta_id',
            '{{%meta}}',
            'id',
            'CASCADE'
        );

        // creates index for column `code_id`
        $this->createIndex(
            '{{%idx-meta_code-code_id}}',
            '{{%meta_code}}',
            'code_id'
        );

        // add foreign key for table `{{%code}}`
        $this->addForeignKey(
            '{{%fk-meta_code-code_id}}',
            '{{%meta_code}}',
            'code_id',
            '{{%code}}',
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
            '{{%fk-meta_code-meta_id}}',
            '{{%meta_code}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-meta_code-meta_id}}',
            '{{%meta_code}}'
        );

        // drops foreign key for table `{{%code}}`
        $this->dropForeignKey(
            '{{%fk-meta_code-code_id}}',
            '{{%meta_code}}'
        );

        // drops index for column `code_id`
        $this->dropIndex(
            '{{%idx-meta_code-code_id}}',
            '{{%meta_code}}'
        );

        $this->dropTable('{{%meta_code}}');
    }
}
