<?php

use yii\db\Migration;

/** Apply before enabling baseline QR capture; contains no login secret. */
class m261012_020000_create_legacy_wechat_scene_evidence extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%identity_legacy_wechat_scenes}}', [
            'scene_hash' => $this->char(64)->notNull(),
            'provider_app_id' => $this->string(128)->notNull(),
            'issued_at' => $this->integer()->notNull(),
            'expires_at' => $this->integer()->notNull(),
            'source' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[scene_hash]])',
        ]);
        $this->createIndex('idx_identity_legacy_wechat_expiry', '{{%identity_legacy_wechat_scenes}}', 'expires_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%identity_legacy_wechat_scenes}}');
    }
}
