<?php
use yii\helpers\Url;


$js = <<<JS

function load_project(json) { }
$(".loading").on('click', function(){  
	 $("#loadMe").modal({
      backdrop: "static", //remove ability to close modal with click
      keyboard: false, //remove option to close with keyboard
      show: true //Display loader!
    });
	//load_project('button3', 13);
	setTimeout(function() {
		$("#loadMe").modal("hide");
    }, 13500);
});
JS;
$this->registerJs($js,\yii\web\View::POS_END);
$css = <<<CS
.loader {
  position: relative;
  text-align: center;
  margin: 15px auto 35px auto;
  z-index: 9999;
  display: block;
  width: 80px;
  height: 80px;
  border: 10px solid rgba(0, 0, 0, .3);
  border-radius: 50%;
  border-top-color: #000;
  animation: spin 1s ease-in-out infinite;
  -webkit-animation: spin 1s ease-in-out infinite;
}


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


			$input = new StdClass();
			$input->projectId = $proj->id;
			$json = json_encode($input, JSON_UNESCAPED_SLASHES);
			$value = urlencode($json);
			$url = 'telnet://project?cb=callback&input='.$value;
			 
			if(Yii::$app->request->get('mode', null) == 'pc'){
			$url = '#';
			}
		?>
		

		
        <div class="col-xs-3 col-md-3">
          <div class="tile">
            <a class="loading btn btn-info btn-large btn-block" onclick='load_project(<?=json_encode($json)?>);' style="height:280px" href="<?=$url?>">
			
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
<div class="modal fade" id="loadMe" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <div class="loader"></div>
        <div clas="loader-txt">
          <p>载入场景中 <br><br><small>系统正在从网络读取数据</small></p>
        </div>
      </div>
    </div>
  </div>
</div>