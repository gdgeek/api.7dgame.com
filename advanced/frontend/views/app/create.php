<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

?>

  
<?php
echo Yii::$app->user->identity->access_token;
$js = <<<JS

    let data = {};
    function setDataPolygenId(polygen_id, name){
        data.polygen_id = polygen_id;
        data.polygen_name = name;
        pt();
    }
   
    function setDataInfo(info){
        data.information =  \$('#info').val();
        show();
        pt();
    }
    function pt(){
       //alert(JSON.stringify(data))
    }
    function setDataTitle(){
        data.title = \$('#title').val();
        pt();
    }
    function show(){
        \$('#card-title').text(data.title);
        \$('#card-info').text(data.information);
        \$('#card-polygen').text(data.polygenName);
    }
    function createProject(){
        execute();
    }
    function execute(){
        $("#execute").attr("href","telnet://create.project?data="+encodeURI(JSON.stringify(data)));
        $("#execute")[0].click();
    }
JS;

  


$this->registerJs($js , View::POS_END);
if(!empty($accessToken)){
   

   

    $this->registerJs("
       $('#execute').attr('href','telnet://access.token?access_token=".$accessToken."');
       $('#execute')[0].click();
    ");
}

?>
      <a id="execute" href="http://baidu.com"></a>
      <section class="flush-with-above">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-12">
              <div class="card card-lg">
                <div class="card-body">
                  <form class="wizard">


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
                     <div class="tab-content">
                          <div id="first">
                          
                    <span> <h6 class="title-decorative mb-2"></h6><h4 class="mb-2">第一步.选择模型</h4></span>
                  

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
                    'class' => 'btn btn-success btn-xs sw-btn-next',
                    'title' => '创建',
                    'onclick'=>'setDataPolygenId('.$data->id.',"'.$data->name.'")',
                    'aria-label' => '创建',
                ];

                return Html::Button(
                    '选取创建',
                    $options
                );
            },
        ],

    ],
]); ?>


                          </div>
                          <div id="second">
                              <div class="row justify-content-around align-items-center">
                                  <div class="col-8 col-md-6 col-lg-4 mb-4">
                                    <img alt="Image" src="<?=Url::to('@web/pages/assets/img/graphic-man-box.svg')?>" class="w-100" />
                                  </div>
                                  <!--end of col-->
                                  <div class="col-12 col-md-6 col-lg-5 mb-4">
                                    <div>
                                      <h6 class="title-decorative mb-2">第二步.</h6>
                                      <h4 class="mb-2">输入场景名称</h4>
                                      <p>这个名称作为场景的唯一标题</p>
                                      <div class="form-group">

                                        <input id="title"  type="text" placeholder="输入一个醒目的名字" name="name" class="form-control form-control-lg"  value ="123"/>
                                      
                                      </div>
                                    </div>
                                    <button class="btn btn-success sw-btn-next" onclick="setDataTitle()" type="button">下一步</button>
                                  </div>
                                  <!--end of col-->
                               </div>
                                <!--end of row-->
                          </div>

                          <div id="third">
                              <div class="row justify-content-around align-items-center">
                                  <div class="col-8 col-md-6 col-lg-4 mb-4">
                                    <img alt="Image" src="<?=Url::to('@web/pages/assets/img/graphic-man-box.svg')?>" class="w-100" />
                                  </div>
                                  <!--end of col-->
                                  <div class="col-12 col-md-6 col-lg-5 mb-4">
                                    <div>
                                      <h6 class="title-decorative mb-2">第三步.</h6>
                                      <h4 class="mb-2">场景相关介绍</h4>
                                      <p>介绍一下相关场景吧。</p>
                                      <div class="form-group">
                                        <textarea id="info" rows='3' name="name" class="form-control form-control-lg" ></textarea>
                                      </div>
                                    </div>
                                    <button class="btn btn-success sw-btn-next" type="button" onclick="setDataInfo()">下一步</button>
                                  </div>
                                  <!--end of col-->
                               </div>
                                <!--end of row-->
                          </div>
                          
                           <div id="fourth">
                              <div class="row justify-content-around align-items-center">
                                  <div class="col-8 col-md-6 col-lg-4 mb-4">
                                    <img alt="Image" src="<?=Url::to('@web/pages/assets/img/graphic-man-box.svg')?>" class="w-100" />
                                  </div>
                                  <!--end of col-->
                                  <div class="col-12 col-md-6 col-lg-5 mb-4">
                                    <div>
                                      <h6 class="title-decorative mb-2">最后一步.</h6>
                                      <h4 class="mb-2">确认信息</h4>
                                      <div class="card">
                                        
                                        <div class="card-body">
                                            <h4 class="card-title" id="card-title">标题</h4>
                                            <p class="card-text text-body"  id="card-info">Snappy UI interaction library with flexible API</p>
                                            <p class="card-text text-body">模型:<b id="card-polygen">模型</b></p>
                                        </div>
                                       
                                      </div>
                                    </div>
                                    
                                    <button class="btn btn-success" type="button" onclick="createProject()">好的，请创建场景</button>
                                  </div>
                                  <!--end of col-->
                               </div>
                                <!--end of row-->
                          </div>

                      </div>

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
     
