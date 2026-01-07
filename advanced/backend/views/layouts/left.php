<?php

use mdm\admin\components\MenuHelper; 
?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
				 <img src="<?=Yii::$app->request->baseUrl?>/public/image/default-avatar.png" class="img-cube" alt="User Image"/>  
            </div>
            <div class="pull-left info">
                <p><?=Yii::$app->user->identity->username?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>


		 
<?php 
		$callback = function($menu){ 
			$data = json_decode($menu['data'], true); 
			$items = $menu['children']; 
			$return = [ 
				'label' => $menu['name'], 
				'url' => [$menu['route']], 
			]; 
			if ($data) { 
				//visible 
				isset($data['visible']) && $return['visible'] = $data['visible']; 
				//icon 
				isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon']; 
				//other attribute e.g. class... 
				$return['options'] = $data; 
			} 
			(!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'circle-o'; 
			$items && $return['items'] = $items; 

			return $return; 
		}; 



		  $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);

		  array_unshift($items, ['label' =>Yii::$app->params['information']['sub-title']]);
		  echo dmstr\widgets\Menu::widget([
			'options' => ['class' => 'sidebar-menu'],
			'items' => $items,
			]); 
		
		
		?>
        <?php /*= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Mixed Reality Programming Platform', 'options' => ['class' => 'header']],
                    ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug']],
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                    [
                        'label' => 'Some tools',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii'],],
                            ['label' => 'Debug', 'icon' => 'dashboard', 'url' => ['/debug'],],
                            [
                                'label' => 'Level One',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'Level Two', 'icon' => 'circle-o', 'url' => '#',],
                                    [
                                        'label' => 'Level Two',
                                        'icon' => 'circle-o',
                                        'url' => '#',
                                        'items' => [
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                            ['label' => 'Level Three', 'icon' => 'circle-o', 'url' => '#',],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        )*/ ?>

    </section>

</aside>
