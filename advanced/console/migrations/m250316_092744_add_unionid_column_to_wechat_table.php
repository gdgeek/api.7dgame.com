<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%wechat}}`.
 */
class m250316_092744_add_unionid_column_to_wechat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%wechat}}', 'unionid', $this->string()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%wechat}}', 'unionid');
    }
}
