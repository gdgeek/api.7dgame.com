<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%url}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%version}}`
 */
class m210503_174243_create_url_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%url}}', [
            'id' => $this->primaryKey(),
            'version' => $this->integer(),
            'key' => $this->string(),
            'value' => $this->string(),
        ]);

        // creates index for column `version`
        $this->createIndex(
            '{{%idx-url-version}}',
            '{{%url}}',
            'version'
        );

        // add foreign key for table `{{%version}}`
        $this->addForeignKey(
            '{{%fk-url-version}}',
            '{{%url}}',
            'version',
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
        // drops foreign key for table `{{%version}}`
        $this->dropForeignKey(
            '{{%fk-url-version}}',
            '{{%url}}'
        );

        // drops index for column `version`
        $this->dropIndex(
            '{{%idx-url-version}}',
            '{{%url}}'
        );

        $this->dropTable('{{%url}}');
    }
}
