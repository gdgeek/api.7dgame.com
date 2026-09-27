<?php

use yii\db\Migration;

class m260926_210000_create_device_sn_table extends Migration
{
    private const ACTIONS = ['index', 'view', 'accounts', 'generate', 'update', 'reveal', 'export'];

    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%device_sn}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'device_uuid' => $this->string(255)->null(),
            'sn_hash' => $this->string(64)->notNull(),
            'sn_ciphertext' => $this->text()->notNull(),
            'key_id' => $this->string(64)->notNull(),
            'sn_tail' => $this->string(4)->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'activated_at' => $this->dateTime()->null(),
            'last_login_at' => $this->dateTime()->null(),
            'remark' => $this->string(500)->notNull()->defaultValue(''),
        ], $options);
        $this->createIndex('uq_device_sn_hash', '{{%device_sn}}', 'sn_hash', true);
        $this->createIndex('uq_device_sn_uuid', '{{%device_sn}}', 'device_uuid', true);
        $this->createIndex('idx_device_sn_user', '{{%device_sn}}', 'user_id');
        $this->createIndex('idx_device_sn_tail', '{{%device_sn}}', 'sn_tail');
        $this->addForeignKey('fk_device_sn_user', '{{%device_sn}}', 'user_id', '{{%user}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_device_sn_creator', '{{%device_sn}}', 'created_by', '{{%user}}', 'id', 'SET NULL');

        foreach (self::ACTIONS as $action) {
            foreach (['sn-management.' . $action, '/v1/plugin-sn/' . $action] as $name) {
                $this->upsert('{{%auth_item}}', [
                    'name' => $name, 'type' => 2, 'description' => 'SN management: ' . $action,
                    'created_at' => time(), 'updated_at' => time(),
                ], false);
                $this->upsert('{{%auth_item_child}}', ['parent' => 'root', 'child' => $name], false);
            }
        }
        // AccessControl's public-route rule is distinct from the explicit authenticator exception.
        foreach (['/v1/auth/sn-activate', '/v1/auth/sn-login'] as $name) {
            $this->upsert('{{%auth_item}}', [
                'name' => $name, 'type' => 2, 'description' => 'SN device authentication',
                'created_at' => time(), 'updated_at' => time(),
            ], false);
        }
    }

    public function safeDown()
    {
        foreach (self::ACTIONS as $action) {
            $names = ['sn-management.' . $action, '/v1/plugin-sn/' . $action];
            $this->delete('{{%auth_item_child}}', ['child' => $names]);
            $this->delete('{{%auth_item}}', ['name' => $names]);
        }
        $this->delete('{{%auth_item}}', ['name' => ['/v1/auth/sn-activate', '/v1/auth/sn-login']]);
        $this->dropTable('{{%device_sn}}');
    }
}
