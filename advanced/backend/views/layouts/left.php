<?php
use mdm\admin\components\MenuHelper;
?>
<!-- LEFT MENU v2026.02.03.003 -->
<aside class="main-sidebar">
    <section class="sidebar">
        <div style="background:#ff0;color:#000;padding:5px;font-size:12px;text-align:center;">v2026.02.03.003</div>
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= Yii::$app->request->baseUrl ?>/public/image/default-avatar.png" class="img-cube" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->username ?? 'Guest' ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <?php
        // 调试信息
        $debugInfo = [];
        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $debugInfo[] = 'User ID: ' . ($userId ?? 'Guest') . ' (type: ' . gettype($userId) . ')';
        $debugInfo[] = 'MenuHelper exists: ' . (class_exists('mdm\admin\components\MenuHelper') ? 'Yes' : 'No');
        
        // 检查 RBAC 数据
        try {
            $db = Yii::$app->db;
            $debugInfo[] = 'DB DSN: ' . substr($db->dsn, 0, 50) . '...';
            
            $manager = Yii::$app->authManager;
            $debugInfo[] = 'AuthManager class: ' . get_class($manager);
            
            // 直接查询 auth_assignment 表
            $allAssignments = (new \yii\db\Query())->from('auth_assignment')->all();
            $debugInfo[] = 'Total auth_assignment rows: ' . count($allAssignments);
            
            // 显示前3条记录的 user_id
            $sampleIds = array_slice(array_column($allAssignments, 'user_id'), 0, 3);
            $debugInfo[] = 'Sample user_ids: ' . json_encode($sampleIds);
            
            // 用不同方式查询当前用户
            $byInt = (new \yii\db\Query())->from('auth_assignment')->where(['user_id' => $userId])->count();
            $byStr = (new \yii\db\Query())->from('auth_assignment')->where(['user_id' => (string)$userId])->count();
            $debugInfo[] = "Query by int($userId): $byInt, by string('$userId'): $byStr";
            
            $assignments = $manager->getAssignments($userId);
            $debugInfo[] = 'Assignments via manager: ' . count($assignments);
            if ($assignments) {
                $debugInfo[] = 'Roles: ' . implode(', ', array_keys($assignments));
            }
            
            $permissions = $manager->getPermissionsByUser($userId);
            $debugInfo[] = 'Permissions count: ' . count($permissions);
            
            // 检查 menu 表
            $menuCount = (new \yii\db\Query())->from('menu')->count();
            $debugInfo[] = 'Menu table rows: ' . $menuCount;
            
            // 检查 auth_item 表
            $authItemCount = (new \yii\db\Query())->from('auth_item')->count();
            $debugInfo[] = 'Auth_item rows: ' . $authItemCount;
            
            // 检查 auth_item_child 表
            $authItemChildCount = (new \yii\db\Query())->from('auth_item_child')->count();
            $debugInfo[] = 'Auth_item_child rows: ' . $authItemChildCount;
            
        } catch (\Throwable $e) {
            $debugInfo[] = 'RBAC check error: ' . $e->getMessage();
            $debugInfo[] = 'Error at: ' . $e->getFile() . ':' . $e->getLine();
        }
        
        try {
            $callback = function ($menu) {
                $data = json_decode($menu['data'], true);
                $items = $menu['children'];
                $return = [
                    'label' => $menu['name'],
                    'url' => [$menu['route']],
                ];
                if ($data) {
                    isset($data['visible']) && $return['visible'] = $data['visible'];
                    isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
                    $return['options'] = $data;
                }
                (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'circle-o';
                $items && $return['items'] = $items;
                return $return;
            };

            $items = [];
            if (!Yii::$app->user->isGuest) {
                $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
                $debugInfo[] = 'Menu items count: ' . count($items);
            } else {
                $debugInfo[] = 'User is guest, no menu loaded';
            }
            
            $subTitle = Yii::$app->params['information']['sub-title'] ?? '';
            if ($subTitle) {
                array_unshift($items, ['label' => $subTitle]);
            }

            // 显示调试信息
            echo '<div style="background:#333;color:#0f0;padding:5px;font-size:10px;margin:5px;">';
            echo implode('<br>', $debugInfo);
            echo '</div>';

            echo dmstr\widgets\Menu::widget([
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => $items,
            ]);
        } catch (\Throwable $e) {
            echo '<div class="alert alert-danger" style="margin: 10px;">';
            echo '<strong>菜单加载错误:</strong><br>';
            echo htmlspecialchars($e->getMessage()) . '<br>';
            echo '<small>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
            echo '</div>';
        }
        ?>
    </section>
</aside>

<?php
$this->registerJs(<<<JS
$(function() {
    var KEY = 'menu_open';
    
    // 恢复展开状态
    var open = JSON.parse(localStorage.getItem(KEY) || '[]');
    open.forEach(function(i) {
        \$('.sidebar-menu .treeview').eq(i).addClass('menu-open').find('> .treeview-menu').show();
    });
    
    // 保存展开状态
    \$('.sidebar-menu').on('click', '.treeview > a', function() {
        setTimeout(function() {
            var open = [];
            \$('.sidebar-menu .treeview.menu-open').each(function() {
                open.push(\$('.sidebar-menu .treeview').index(this));
            });
            localStorage.setItem(KEY, JSON.stringify(open));
        }, 50);
    });
});
JS
, \yii\web\View::POS_END);
?>
