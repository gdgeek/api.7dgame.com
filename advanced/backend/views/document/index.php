<?php
/* @var $this yii\web\View */
$this->title = "欢迎页面";


$this->blocks['subTitle'] = '平台介绍';
$this->params['breadcrumbs'][] = ['label' => '相关文档', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (!Yii::$app->params['information']['local']) {


  $this->registerJs('



	$.get("https://hololens2.cn/wp-json/wp/v2/posts/872", function(result){
		//alert(result);	
		
		$("#title").html(result.title.rendered);
		$("#content").html(result.content.rendered);
		$("#date").html(result.date);
		console.log(result);
	  });

	
');


?>

  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          <i class="fa fa-globe"></i>
          <mrpp id="title"></mrpp>
          <small class="pull-right" id="date">Date: 2/10/2014</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <p class="text-muted well well-sm no-shadow" id="content" style="margin: 20px;">
        载入中....
      </p>
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->


<?php
} else {

?>


  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          <i class="fa fa-globe"></i>
          <mrpp id="title">混合现实平台直播版本</mrpp>
          <small class="pull-right" id="date">Date: 2/10/2014</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <p class="text-muted well well-sm no-shadow" id="content" style="margin: 20px;">
        混合现实平台，直播定制版本
      </p>
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->



<?php

}


?>