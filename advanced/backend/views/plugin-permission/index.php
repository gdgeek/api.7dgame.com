<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PluginPermissionConfigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '插件权限配置';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plugin-permission-config-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('新增配置', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'role_or_permission',
            'plugin_name',
            'action',
            'created_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => '删除',
                            'data-confirm' => '确定要删除此配置吗？',
                            'data-method' => 'post',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
