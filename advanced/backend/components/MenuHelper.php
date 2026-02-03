<?php

namespace backend\components;

use Yii;
use mdm\admin\models\Menu;

/**
 * 自定义 MenuHelper，支持 /* 通配符匹配所有菜单（包括 @manager 前缀）
 */
class MenuHelper
{
    /**
     * 获取用户分配的菜单
     * 如果用户有 /* 权限，则返回所有菜单
     */
    public static function getAssignedMenu($userId, $root = null, $callback = null, $refresh = false)
    {
        $manager = Yii::$app->authManager;
        $permissions = $manager->getPermissionsByUser($userId);
        
        // 检查是否有 /* 通配符权限
        $hasWildcard = isset($permissions['/*']);
        
        if ($hasWildcard) {
            // 有通配符权限，返回所有菜单
            $menus = Menu::find()->asArray()->indexBy('id')->all();
            $assigned = array_keys($menus);
            return static::normalizeMenu($assigned, $menus, $callback, $root);
        }
        
        // 否则使用原始逻辑
        return \mdm\admin\components\MenuHelper::getAssignedMenu($userId, $root, $callback, $refresh);
    }

    /**
     * 格式化菜单
     */
    private static function normalizeMenu(&$assigned, &$menus, $callback, $parent = null)
    {
        $result = [];
        $order = [];
        foreach ($assigned as $id) {
            if (!isset($menus[$id])) continue;
            $menu = $menus[$id];
            if ($menu['parent'] == $parent) {
                $menu['children'] = static::normalizeMenu($assigned, $menus, $callback, $id);
                if ($callback !== null) {
                    $item = call_user_func($callback, $menu);
                } else {
                    $item = [
                        'label' => $menu['name'],
                        'url' => static::parseRoute($menu['route']),
                    ];
                    if ($menu['children'] != []) {
                        $item['items'] = $menu['children'];
                    }
                }
                $result[] = $item;
                $order[] = $menu['order'];
            }
        }
        if ($result != []) {
            array_multisort($order, $result);
        }
        return $result;
    }

    /**
     * 解析路由
     */
    public static function parseRoute($route)
    {
        if (!empty($route)) {
            $url = [];
            $r = explode('&', $route);
            $url[0] = $r[0];
            unset($r[0]);
            foreach ($r as $part) {
                $part = explode('=', $part);
                $url[$part[0]] = isset($part[1]) ? $part[1] : '';
            }
            return $url;
        }
        return '#';
    }
}
