<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->registerJsFile("@web/libs/js/cos-js-sdk-v5.js");
$this->registerJsFile("@web/libs/js/MrPP-cos.js");
$this->registerJsFile("@web/libs/js/spark-md5.js");

$js = <<<JS

$('button[data-toggle="tooltip"]').tooltip({
    animated: 'fade',
    placement: 'bottom',
    html: true
});

function file_del(id){
    $("#materialform-"+id).val(null);
    
    
    $("#md5-progressbar").css("width",0+"%");
    $("#md5-progresstext").text(Math.round(0)+"%");
    
    $("#upload-progressbar").css("width", 0 +"%");
    $("#upload-progresstext").text(Math.round(0)+"%");
    
    $("#old-progressbar").css("width", 0 +"%");
    $("#old-progresstext").text(Math.round(0)+"%");
    
    $("#Video_del").hide();
	$("#Video").removeClass('btn-success');
    $("#Video").removeClass('btn-info');
	$("#Video").addClass('btn-default');
	$("#Video").attr("title",  "选择上传文件").tooltip('fixTitle');
	$("#Video").html("上传")
	$(".submit").removeAttr("disabled");
}

function progressbar(md5, upload){
    
    $("#md5-progressbar").css("width",md5+"%");
    $("#md5-progresstext").text(Math.round(md5)+"%");
    
    $("#upload-progressbar").css("width", upload +"%");
    $("#upload-progresstext").text(Math.round(upload)+"%");
    
    $("#old-progressbar").css("width", (100-md5-upload) +"%");
    $("#old-progresstext").text(Math.round((100-md5-upload))+"%");
}
$(".upload").click(function(){
	let id = $(this).attr('id');
	file_open("video/mp4",
		function(file){
			file_md5(file, 
				function(p){
			        progressbar(id, p, 0)
				},
				function(md5)
				{
                    
                    const filename = md5+type;
					file_has(filename, function(has){
						if(has){
							file_save(filename, id, md5, file.type, file.name);
			                progressbar(0, 100);
						}else{
							file_upload(filename, md5, file, 
							function(p)
							{
			                    progressbar( p, 100-p);
							},
							function(){
			                    progressbar(0, 100);
								file_save(filename, id, md5, file.type, file.name);
							});
						}
					});
				}
			)
		}
	);
});
	
function file_save(filename, id, md5, type, name){
	let val = {};
	val.md5 = md5;
	val.url = file_url(filename);
	val.type = type;
	val.name = name;
	$("#video-file_id").val(JSON.stringify(val));
	$("#Video").removeClass('btn-info');
	$("#Video").removeClass('btn-default');
	$("#Video").addClass('btn-success');
    $("#Video_del").show();
	$("#Video").attr("title",  "<img width='100px'; src='"+val.url+"'>").tooltip('fixTitle').tooltip('show');
	$(".submit").removeAttr("disabled");
}

JS;
$url = Url::toRoute(['cos/auth', 'bucket' => 'files-1257979353', 'region' => 'ap-chengdu']);
$config = <<<JS
let config = {
    Bucket: 'files-1257979353',
    Region: 'ap-chengdu',
	Url:"$url",
};
JS;
$this->registerJs($js);
$this->registerJs($config, View::POS_BEGIN);


/* @var $this yii\web\View */
/* @var $model backend\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="Video-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
    <?= Html::activeHiddenInput($model, 'file_id') ?>
    <div class="media">
        <div class="media-left">
            <div style="white-space: nowrap;">
                <?PHP
                $options = ['class' => 'btn btn-default btn-xs upload',
                    'data-toggle' => "tooltip",
                    'title'=>\Yii::t('app', 'Select Video'),
                    'id' => 'Video'
                ];
                echo Html::Button(\Yii::t('app', 'Upload Video'), $options);

                $options = ['class' => 'btn btn-link btn-xs',
                    'title' => \Yii::t('app', 'Delete'),
                    'aria-label' => 'Delete',
                    'data-toggle' => "tooltip",
                    'id'=>"Video_del",
                    'onclick'=>"file_del()",
                    'style'=>'display:none',
                ];
                echo Html::Button('X', $options);
                ?>
            </div>
        </div>
        <div class="media-body">
            <div class="progress">
                <div id="upload-progressbar" class="progress-bar progress-bar-success" style="width: 0">
                    <span id="upload-progresstext" class="sr-only">0% 完成 (上传)</span>
                </div>
                <div id="md5-progressbar" class="progress-bar progress-bar-warning progress-bar-striped" style="width: 0">
                    <span id="md5-progresstext" class="sr-only">0% 完成 (md5)</span>
                </div>
                <div id="old-progressbar" class="progress-bar progress-bar-info" style="width: 100% ">
                    <span id="old-progresstext" class="sr-only">0% 完成 (上传)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
