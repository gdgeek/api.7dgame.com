<?php


use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;





$this->title = '上传模型';
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile("@web/coslib/dist/cos-js-sdk-v5.min.js");
/* @var $this yii\web\View */
$url = Url::toRoute('cos/sts');
$js = <<<JS
var config = {
    Bucket: 'fbx-1257979353',
    Region: 'ap-chengdu'
};




var getAuthorization = function (options, callback) {

    // 格式一、（推荐）后端通过获取临时密钥给到前端，前端计算签名
    // 服务端 JS 和 PHP 例子：https://github.com/tencentyun/cos-js-sdk-v5/blob/master/server/
    // 服务端其他语言参考 COS STS SDK ：https://github.com/tencentyun/qcloud-cos-sts-sdk
    // var url = 'http://127.0.0.1:3000/sts';
    var url = '$url';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onload = function (e) {
        try {
            var data = JSON.parse(e.target.responseText);
            var credentials = data.credentials;
        } catch (e) {
        }
        callback({
            TmpSecretId: credentials.tmpSecretId,
            TmpSecretKey: credentials.tmpSecretKey,
            XCosSecurityToken: credentials.sessionToken,
            ExpiredTime: data.expiredTime, // SDK 在 ExpiredTime 时间前，不会再次调用 getAuthorization
        });
    };
    xhr.send();



};

var showLogText = function (text, color) {
    if (typeof text === 'object') {
        try {
            text = JSON.stringify(text);
        } catch (e) {
        }
    }
    var div = document.createElement('div');
    div.innerText = text;
    color && (div.style.color = color);
	/////////!!!!!?????
    //pre.appendChild(div);
   // pre.style.display = 'block';
   // pre.scrollTop = pre.scrollHeight;
};


var logger = {
    log: function (text) {
        console.log.apply(console, arguments);
        var args = [].map.call(arguments, function (v) {
            return typeof v === 'object' ? JSON.stringify(v) : v;
        });
        showLogText(args.join(' '));
    },
    error: function (text) {
        console.error(text);
        showLogText(text, 'red');
    },
};
var cos = new COS({
    getAuthorization: getAuthorization
});

var pre = document.querySelector('.result');
function selectFileToUpload(filename) {
    var input = document.createElement('input');
    input.type = 'file';
	input.accept = '.fbx';
    input.onchange = function (e) {
        var file = this.files[0];
        if (file) {
		
			$('#upload').text('正在上传');
			$("#upload").attr("disabled",true);

			if(1024 * 1024 * 50 <= file.size){
				alert('超过50M的模型，在某些设备上可能会无法运行。')
			}
            if (file.size > 1024 * 1024) {

                cos.sliceUploadFile({
                    Bucket: config.Bucket, // Bucket 格式：test-1250000000
                    Region: config.Region,
                    Key: filename,
                    Body: file,
                    onTaskReady: function (tid) {
                        TaskId = tid;
                    },
                    onHashProgress: function (progressData) {
                        logger.log('onHashProgress', JSON.stringify(progressData.percent));
                    },
                    onProgress: function (progressData) {
						var width = Math.round(progressData.percent*100)+'%';
						$("#bar").css('width', width);
						$("#bar_text").text(width);
					
                        logger.log('上传中('+Math.round(progressData.percent*100)+'%)');
                    },
                }, function (err, data) {
					$('#uploadform-filename').val(data.Location);
                    logger.log(err || '上传完成');
					$('#upload').text('上传完成');
					
					$("#uploadform-title").attr("disabled",false);
					$('#w0').submit();
                });
            } else {
                cos.putObject({
                    Bucket: config.Bucket, // Bucket 格式：test-1250000000
                    Region: config.Region,
                    Key: filename,
                    Body: file,
                    onTaskReady: function (tid) {
                        TaskId = tid;
                    },
                    onProgress: function (progressData) {
                       
						var width = Math.round(progressData.percent*100)+'%';
						$("#bar").css('width', width);
						$("#bar_text").text(width);
					
                        logger.log('上传中('+Math.round(progressData.percent*100)+'%)');
                    },
                }, function (err, data) {
					$('#uploadform-filename').val(data.Location);
                    logger.log(err || '上传完成');
					$('#upload').text('上传完成');
					$("#uploadform-title").attr("disabled",false);
					$('#w0').submit();
                });
            }
        }
    };
    input.click();
}

function checkUpload(){

	var val=$("#uploadform-title").val();
	if(val.length>0){
		$("#uploadform-title").next().html('');
		$("#uploadform-title").attr("disabled",true);
		selectFileToUpload("$filename.fbx")
	}else{
		$("#uploadform-title").next().html('<p class="text-danger">标题不能为空</p>');
	}
}


JS;

$this->registerJs($js,\yii\web\View::POS_END);
?>



<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>



	<?= Html::activeHiddenInput($model,'filename') ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'disabled'=>false]) ?>
    <?= $form->field($model, 'sharing')->checkbox() ?>

	<div class="progress">
  <div id="bar" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
    <div id="bar_text"></div>
  </div>
</div>
	<?PHP

	$options = ['class' => 'btn btn-success ',
					'title' => '上传文件',
					'aria-label'=> '上传文件',
					'onclick' => 'checkUpload()',
					'id' => 'upload'
					];
	echo Html::Button("上传文件 (FBX格式)", $options);
	
	?>
 

<?php ActiveForm::end() ?>