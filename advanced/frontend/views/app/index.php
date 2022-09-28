<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
?>

  

      
      <section class="flush-with-above">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-12">
              <div class="card card-lg">
                <div class="card-body">
                  <form class="wizard">

                  <div class="text-center">
                    <span><h3>选择操作模型</h3><br/></span>
                  </div>


                    <ul class="nav nav-tabs text-center row justify-content-center">
                      <li class="col-3 col-md-2"><a href="#first" class="step-circle step-circle-sm">1</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#second" class="step-circle step-circle-sm">2</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#third" class="step-circle step-circle-sm">3</a>
                      </li>
                      <li class="col-3 col-md-2"><a href="#fourth" class="step-circle step-circle-sm">4</a>
                      </li>
                    </ul>

                    
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout'=>"{items}\n{pager}",
    'tableOptions'=> ['class' => 'table table-borderless table-hover align-items-center'],
    'rowOptions' => function($model, $key, $index, $grid) {
		return ['class' => 'bg-white'];
	},
    'afterRow'=>function($model,$key, $index,$grid){
        return '<tr class="table-divider"><th></th><td></td></tr>';
    },
       

    'columns' => [

            [
            'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
            'label' => Yii::t('app', 'Name'),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['name'];
            },
            'attribute' => 'name',
        ],
        [
            'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
            'label' => Yii::t('app', 'Created At'),
            'format' => 'raw',
            'value' => function ($data) {
                return $data['created_at'];
            },
            'attribute' => 'created_at',
        ],


        [
            'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
            'label' => '操作',
            'format' => 'raw',
            'value' => function ($data) {


                $options = [
                    'class' => 'btn btn-success btn-xs',
                    'title' => '编辑',
                    'aria-label' => '编辑',
                ];

                return Html::a(
                    '<span class="glyphicon glyphicon-picture" aria-hidden="true"></span> 选取创建',
                    ['create-second', 'polygen_id' => $data['id'], '#' => 'second'],
                    $options
                );
            },
        ],

    ],
]); ?>


                  </form>
                </div>
              </div>
            
            </div>
            <!--end of col-->
          </div>
          <!--end of row-->
        </div>
        <!--end of container-->
       
      </section>
      <!--end of section-->
     