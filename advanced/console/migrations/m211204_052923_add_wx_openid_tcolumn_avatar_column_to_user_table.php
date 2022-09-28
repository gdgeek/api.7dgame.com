<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m211204_052923_add_wx_openid_tcolumn_avatar_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'wx_openid', $this->string());
        $this->addColumn('{{%user}}', 'avatar', $this->string());

        // creates index for column `wx_openid`
        $this->createIndex(
            '{{%user-wx_openid}}',
            '{{%user}}',
            'wx_openid'
        );



    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropIndex(
            '{{%user-wx_openid}}',
            '{{%user}}'
        );

     

        $this->dropColumn('{{%user}}', 'wx_openid');
        $this->dropColumn('{{%user}}', 'avatar');
    }
}
