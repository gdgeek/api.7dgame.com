<?php

use yii\db\Expression;
use yii\db\Migration;

class m260330_000400_seed_plugin_db extends Migration
{
    public $db = 'pluginDb';

    public function init()
    {
        $this->db = 'pluginDb';
        parent::init();
    }

    public function safeUp()
    {
        $toolsGroupI18n = json_encode([
            'zh-CN' => '实用工具',
            'zh-TW' => '實用工具',
            'en-US' => 'Utilities',
            'ja-JP' => 'ユーティリティ',
            'th-TH' => 'เครื่องมือ',
        ], JSON_UNESCAPED_UNICODE);

        $adminGroupI18n = json_encode([
            'zh-CN' => '系统管理',
            'en-US' => 'Administration',
        ], JSON_UNESCAPED_UNICODE);

        $userManagementI18n = json_encode([
            'zh-CN' => '用户管理',
            'zh-TW' => '用戶管理',
            'en-US' => 'User Management',
            'ja-JP' => 'ユーザー管理',
            'th-TH' => 'การจัดการผู้ใช้',
        ], JSON_UNESCAPED_UNICODE);

        $systemAdminI18n = json_encode([
            'zh-CN' => '系统管理',
            'en-US' => 'System Admin',
        ], JSON_UNESCAPED_UNICODE);

        $this->db->createCommand()->upsert('{{%plugin_menu_groups}}', [
            'id' => 'tools',
            'name' => '实用工具',
            'name_i18n' => $toolsGroupI18n,
            'icon' => 'Tools',
            'order' => 2,
            'domain' => null,
        ], [
            'name' => '实用工具',
            'name_i18n' => $toolsGroupI18n,
            'icon' => 'Tools',
            'order' => 2,
            'domain' => null,
        ])->execute();

        $this->db->createCommand()->upsert('{{%plugin_menu_groups}}', [
            'id' => 'admin',
            'name' => '系统管理',
            'name_i18n' => $adminGroupI18n,
            'icon' => 'Setting',
            'order' => 99,
            'domain' => null,
        ], [
            'name' => '系统管理',
            'name_i18n' => $adminGroupI18n,
            'icon' => 'Setting',
            'order' => 99,
            'domain' => null,
        ])->execute();

        $this->db->createCommand()->upsert('{{%plugins}}', [
            'id' => 'user-management',
            'name' => '用户管理',
            'name_i18n' => $userManagementI18n,
            'description' => '用户增删改查管理工具',
            'url' => 'https://user-manager.plugins.xrugc.com/',
            'icon' => 'User',
            'group_id' => 'tools',
            'enabled' => 1,
            'order' => 2,
            'allowed_origin' => 'https://user-manager.plugins.xrugc.com',
            'version' => '1.0.0',
            'domain' => null,
        ], [
            'name' => '用户管理',
            'name_i18n' => $userManagementI18n,
            'description' => '用户增删改查管理工具',
            'url' => 'https://user-manager.plugins.xrugc.com/',
            'icon' => 'User',
            'group_id' => 'tools',
            'enabled' => 1,
            'order' => 2,
            'allowed_origin' => 'https://user-manager.plugins.xrugc.com',
            'version' => '1.0.0',
            'domain' => null,
        ])->execute();

        $this->db->createCommand()->upsert('{{%plugins}}', [
            'id' => 'system-admin',
            'name' => '系统管理',
            'name_i18n' => $systemAdminI18n,
            'description' => '插件权限和注册表管理',
            'url' => 'https://system-admin.plugins.xrugc.com/',
            'icon' => 'Setting',
            'group_id' => 'admin',
            'enabled' => 1,
            'order' => 1,
            'allowed_origin' => 'https://system-admin.plugins.xrugc.com',
            'version' => '1.0.0',
            'domain' => null,
        ], [
            'name' => '系统管理',
            'name_i18n' => $systemAdminI18n,
            'description' => '插件权限和注册表管理',
            'url' => 'https://system-admin.plugins.xrugc.com/',
            'icon' => 'Setting',
            'group_id' => 'admin',
            'enabled' => 1,
            'order' => 1,
            'allowed_origin' => 'https://system-admin.plugins.xrugc.com',
            'version' => '1.0.0',
            'domain' => null,
        ])->execute();

        $this->db->createCommand()->upsert('{{%plugin_permission_config}}', [
            'role_or_permission' => 'root',
            'plugin_name' => 'system-admin',
            'action' => 'manage-permissions',
        ], [
            'updated_at' => new Expression('CURRENT_TIMESTAMP'),
        ])->execute();

        $this->db->createCommand()->upsert('{{%plugin_permission_config}}', [
            'role_or_permission' => 'root',
            'plugin_name' => 'system-admin',
            'action' => 'manage-plugins',
        ], [
            'updated_at' => new Expression('CURRENT_TIMESTAMP'),
        ])->execute();
    }

    public function safeDown()
    {
        $this->delete('{{%plugin_permission_config}}', [
            'role_or_permission' => 'root',
            'plugin_name' => 'system-admin',
        ]);

        $this->delete('{{%plugins}}', ['id' => ['user-management', 'system-admin']]);
        $this->delete('{{%plugin_menu_groups}}', ['id' => ['tools', 'admin']]);
    }
}