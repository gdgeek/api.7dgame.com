<?php

use yii\db\Migration;

/**
 * 创建 I18N 翻译表
 */
class m251205_000001_create_i18n_tables extends Migration
{
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        // 消息源表（存储原文）
        $this->createTable('{{%source_message}}', [
            'id' => $this->primaryKey(),
            'category' => $this->string(255)->notNull()->defaultValue('app'),
            'message' => $this->text(),
        ], $tableOptions);
        $this->createIndex('idx_source_message_category', '{{%source_message}}', 'category');

        // 翻译表（存储各语言翻译）
        $this->createTable('{{%message}}', [
            'id' => $this->integer()->notNull(),
            'language' => $this->string(16)->notNull(),
            'translation' => $this->text(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_message', '{{%message}}', ['id', 'language']);
        $this->addForeignKey(
            'fk_message_source',
            '{{%message}}',
            'id',
            '{{%source_message}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        // 先删除外键
        $this->dropForeignKey('fk_message_source', '{{%message}}');
        // 再删除表
        $this->dropTable('{{%message}}');
        $this->dropTable('{{%source_message}}');
    }
}
