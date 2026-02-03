<?php
use mdm\admin\components\MenuHelper;
?>
<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= Yii::$app->request->baseUrl ?>/public/image/default-avatar.png" class="img-cube" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->username ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <?php
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

        $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
        array_unshift($items, ['label' => Yii::$app->params['information']['sub-title']]);

        echo dmstr\widgets\Menu::widget([
            'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
            'items' => $items,
        ]);
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
        $('.sidebar-menu .treeview').eq(i).addClass('menu-open').find('> .treeview-menu').show();
    });
    
    // 保存展开状态
    $('.sidebar-menu').on('click', '.treeview > a', function() {
        setTimeout(function() {
            var open = [];
            $('.sidebar-menu .treeview.menu-open').each(function() {
                open.push($('.sidebar-menu .treeview').index(this));
            });
            localStorage.setItem(KEY, JSON.stringify(open));
        }, 50);
    });
});
JS
, \yii\web\View::POS_END);
?>
