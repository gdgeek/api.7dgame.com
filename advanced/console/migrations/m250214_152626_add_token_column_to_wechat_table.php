<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wechat}}`.
 */
class m250214_152626_add_token_column_to_wechat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wechat}}', 'token', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wechat}}', 'token');
    }
}
