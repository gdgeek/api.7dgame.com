<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%file}}`.
 */
class m220125_134442_add_key_column_to_file_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%file}}', 'key', $this->string()->defaultValue('nokey')->notNull());

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-file-key}}',
            '{{%file}}',
            'key'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-file-key}}',
            '{{%file}}'
        );

        $this->dropColumn('{{%file}}', 'key');
    }
}
//select * from `file_store` where (select count(1) as num from `file` where file.key = file_store.key) = 0