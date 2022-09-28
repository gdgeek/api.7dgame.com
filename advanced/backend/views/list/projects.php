<?php
use yii\helpers\Url;

use yii\bootstrap\Modal;

$requestUrl = Url::toRoute(['list/qrcode']);
$js = <<<JS

function load_project(id) { 

	 $.get('{$requestUrl}', {'id':id},
		function (data) {
			$("#modal-body").html(data);
		}  
	);


	$("#modal-body").html("Loading...");
	$("#loadMe").modal({
      backdrop: "static", //remove ability to close modal with click
      keyboard: false, //remove option to close with keyboard
      show: true //Display loader!
    });
	
	//load_project('button3', 13);
	setTimeout(function() {
		$("#loadMe").modal("hide");
    }, 13500);

}

JS;
$this->registerJs($js,\yii\web\View::POS_END);
$css = <<<CS


@keyframes spin {
  to {
    -webkit-transform: rotate(360deg);
  }
}

@-webkit-keyframes spin {
  to {
    -webkit-transform: rotate(360deg);
  }
}

CS;


$this->registerCss($css);
?>


 <div class="bs-example" data-example-id="thumbnails-with-custom-content">
    <div class="row">
	
		<div class="row demo-tiles">
		<?php
		foreach($projects as $proj){
			?>
		

		
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<div class="tile">
				<a class="loading btn btn-info btn-large btn-block" project_id="123" onclick='load_project(<?=$proj->id?>);' style="height:280px" href="#">
			
				<h3 class="tile-title"><b><?=$proj->title?></b></h3>
				<img class="tile-image " style='' alt="Generic placeholder image" src="<?=Yii::$app->params['identicon']->getImageDataUri($proj->id.Yii::$app->user->id, 64); ?>">
				<p><?=$proj->introduce?></p>
				</a>
				</div>
			</div>
		<?php
		}
	
		?>
      
	  
		</div>

	</div>

</div><!-- /.bs-example -->

  

<!-- Modal -->
<div class="modal fade" id="loadMe" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel" aria-hidden="true">
	<div class="modal-dialog ">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">二维码!</h4>
			</div>
			<div class="modal-body" id="modal-body">
			Loading...
			</div>
			<div class="modal-footer">
				<a href="#" class="btn btn-primary" data-dismiss="modal" >关闭</a>
			</div>
		</div>
	</div>
</div>