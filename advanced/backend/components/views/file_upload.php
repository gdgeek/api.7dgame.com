<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use backend\assets\FileAsset;

FileAsset::register($this);


if(Yii::$app->params['information']['disk']){

    $path = Url::to(['/files/upload']);
    $upload = Url::to(['/file/upload']);
    $url = substr(Url::home(true), 0, strlen(Url::home(true))-1);
    $this->registerJs("let config = {path: '$path', url: '$url', upload: '$upload'};",\yii\web\View::POS_BEGIN);
    $this->registerJsFile("@web/public/libs/file/file-local.js?".time());

}else{

    $url = Url::toRoute(['cos/auth', 'bucket' => $bucket, 'region' => $region]);
    $this->registerJs("let config = { Bucket: '$bucket', Region: '$region',Url:'$url',};",\yii\web\View::POS_BEGIN);
}




$js = '
function compressed_progressbar(p){

    $("#compressed-progressbar").css("width", p+"%");
    $("#md5-progressbar").css("width","0%");
    $("#upload-progressbar").css("width", "0%");

}

function md5_progressbar(p){

    $("#compressed-progressbar").css("width", (100-p)+"%");
    $("#md5-progressbar").css("width",p+"%");
    $("#upload-progressbar").css("width", 0 +"%");

}

function upload_progressbar(p){
    $("#compressed-progressbar").css("width", 0+"%");
    $("#md5-progressbar").css("width",(100-p)+"%");
    $("#upload-progressbar").css("width", p +"%");
}

function file_save(filename, md5, type){
    $("#fileuploadform-md5").val(md5);
    $("#fileuploadform-filename").val(filename);
    $("#fileuploadform-url").val(file_url(filename));
    $("#fileuploadform-type").val(type);
    $("#fileuploadform-title").attr("disabled",false);
    $("#upload").attr("disabled", false);
    $("#w0").submit();
}


function file_select(){
	
    file_open("'.$fileType.'",
		function(file){
	        let val=$("#fileuploadform-title").val();
            if(val.length<=0){
                $("#fileuploadform-title").val(file.name);
            }
            $("#fileuploadform-title").attr("disabled", true);
	        $("#upload").attr("disabled", true);'.($compressed?'
            file_compressed(file, 
            function(p){ compressed_progressbar(p);},
            function(file){
              ':'').'
                file_md5(file, function(p){
                    md5_progressbar(p);
				},
				function(md5)
				{

				    md5_progressbar(100);
                    let type = file.extension'.($compressed?' +".zip"':'').';
                    const filename = md5+type;
				    file_has(filename, function(has){
				    	    
				    	if(has){
                            upload_progressbar(100);
                           
							file_save(filename, md5, type);
	                        $("#upload").attr("disabled", false);
				    	}else{
				    	    file_upload(filename, md5, file, 
                                function(p)
                                {
                                    upload_progressbar(p);
                                },
                                function(){
                                    upload_progressbar(100);
                                    file_save(filename, md5, type);
	                                $("#upload").attr("disabled", false);
                                });
				    	        
				    	}
				    	    
				    });
				    
				});
         '.($compressed?' });':'').'
		});
    
}
';

$this->registerJs($js,\yii\web\View::POS_END);

?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= Html::activeHiddenInput($model,'url') ?>
    <?= Html::activeHiddenInput($model,'md5') ?>
    <?= Html::activeHiddenInput($model,'type') ?>
    <?= Html::activeHiddenInput($model,'filename') ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'disabled'=>false])->label(Yii::t('app', "Title")) ?>
    <div class="progress">
        <div id="upload-progressbar" class="progress-bar progress-bar-success progress-bar-striped" style="width: 0%">
            <?=\Yii::t('app', 'Upload')?>
        </div>
        <div id="md5-progressbar" class="progress-bar progress-bar-info progress-bar-striped" style="width: 0%">
            <?=\Yii::t('app', 'MD5')?>
        </div>
     <?php 
     if($compressed){
     ?>
        <div id="compressed-progressbar" class="progress-bar progress-bar-warning progress-bar-striped" style="width: 0%">
            <?=\Yii::t('app', 'Compress')?>
        </div>
          <?php 
   }
     ?>
    </div>

<?php

echo Html::Button($button, ['class' => 'btn btn-success  btn-xs', 'onclick'=>'file_select()', 'id'=>'upload']);
?>



<?php ActiveForm::end()?>