<?php

use yii\db\Migration;

/** Add projection bookkeeping without changing live legacy account ownership. */
class m261012_010000_create_identity_business_projection_state extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%identity_user_projection_state}}', [
            'user_id' => $this->integer()->notNull(),
            'version' => $this->bigInteger()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[user_id]])',
        ]);
        $this->createTable('{{%identity_projection_checkpoint}}', [
            'name' => $this->string(64)->notNull(),
            'cursor' => $this->bigInteger()->notNull()->defaultValue(0),
            'PRIMARY KEY ([[name]])',
        ]);
        $this->insert('{{%identity_projection_checkpoint}}', ['name' => 'identity-users', 'cursor' => 0]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%identity_projection_checkpoint}}');
        $this->dropTable('{{%identity_user_projection_state}}');
    }
}
