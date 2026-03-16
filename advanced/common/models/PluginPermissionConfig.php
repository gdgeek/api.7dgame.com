<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%plugin_permission_config}}".
 *
 * @property int $id
 * @property string $role_or_permission RBAC 角色或权限名称
 * @property string $plugin_name 插件标识
 * @property string $action 允许的操作
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PluginPermissionConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_permission_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_or_permission', 'plugin_name', 'action'], 'required'],
            [['role_or_permission'], 'string', 'max' => 64],
            [['plugin_name'], 'string', 'max' => 128],
            [['action'], 'string', 'max' => 512],
            [
                ['role_or_permission', 'plugin_name', 'action'],
                'unique',
                'targetAttribute' => ['role_or_permission', 'plugin_name', 'action'],
                'message' => '该配置已存在',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_or_permission' => '角色/权限',
            'plugin_name' => '插件标识',
            'action' => '操作',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 检查指定角色列表是否有权限执行某插件操作
     * action 字段支持逗号分隔存储多个操作，如 "list-users,create-user,update-user"
     *
     * @param string[] $roles 用户的 RBAC 角色列表
     * @param string $pluginName 插件标识
     * @param string $action 操作标识
     * @return bool
     */
    public static function checkPermission(array $roles, string $pluginName, string $action): bool
    {
        if (empty($roles)) {
            return false;
        }
        if (in_array('root', $roles, true)) {
            return true;
        }
        $rows = static::find()
            ->select(['action'])
            ->where(['plugin_name' => $pluginName])
            ->andWhere(['role_or_permission' => $roles])
            ->column();
        foreach ($rows as $actionStr) {
            $actions = array_map('trim', explode(',', $actionStr));
            if (in_array($action, $actions, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取指定角色列表在某插件下的所有允许操作
     * action 字段支持逗号分隔存储多个操作
     *
     * @param string[] $roles 用户的 RBAC 角色列表
     * @param string $pluginName 插件标识
     * @return string[] 去重后的操作列表
     */
    public static function getAllowedActions(array $roles, string $pluginName): array
    {
        if (empty($roles)) {
            return [];
        }
        // root 角色拥有所有权限
        if (in_array('root', $roles, true)) {
            return ['*'];
        }
        $rows = static::find()
            ->select(['action'])
            ->where(['plugin_name' => $pluginName])
            ->andWhere(['role_or_permission' => $roles])
            ->column();
        $result = [];
        foreach ($rows as $actionStr) {
            $actions = array_map('trim', explode(',', $actionStr));
            $result = array_merge($result, $actions);
        }
        return array_values(array_unique($result));
    }
}
