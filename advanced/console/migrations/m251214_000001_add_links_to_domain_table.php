<?php

use yii\db\Migration;

/**
 * 给 domain 表添加 links 字段（JSON 类型）
 */
class m251214_000001_add_links_to_domain_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // MySQL 5.7+ 支持 JSON 类型
        $this->addColumn('{{%domain}}', 'links', $this->json()->null()->comment('链接配置 (JSON)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%domain}}', 'links');
    }
}
