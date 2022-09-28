<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\FileAsset;


FileAsset::register($this);
use yii\widgets\ActiveForm;
$name =  \yii\helpers\Html::encode($model->name) ;

$this->registerJs('
function OpenModal(id, self){
    $("#model_id").val(id);
    $("#model_name").val(self.value);
    $("#modal-default").modal();
}
',  \yii\web\View::POS_BEGIN);

if ($model->image == null) {
    if(Yii::$app->params['information']['disk']) {

        $path = Url::to(['/files/upload']);
        $upload = Url::to(['/file/upload']);
        $url = substr(Url::home(true), 0, strlen(Url::home(true))-1);
        $this->registerJs("let config = {path: '$path', url: '$url', upload: '$upload'};",\yii\web\View::POS_BEGIN);
    } else {

        $bucket = 'files-1257979353';
        $region = 'ap-chengdu';
        $url = Url::toRoute(['cos/auth', 'bucket' => $bucket, 'region' => $region]);
        $this->registerJs("let config = { Bucket: '$bucket', Region: '$region',Url:'$url',};", \yii\web\View::POS_BEGIN);
    }
}
?>
<script>
<?php if ($model->image == null) { ?>
    const image_type = "image/jpeg";
    function progress(bar, description) {
        $('#progress-bar').css('width', bar + '%');
        $('#progress-description').text(description);
    }
// 截图函数
    function loaded() {
        prepare(function(fileData) {
            console.log(fileData);
            $.post('<?= Url::toRoute(['prepare']) ?>', {
                id: <?= $model->id ?>,
                image: fileData,
            }, function(data) {
                progress(100, '处理完毕');
                $('#loading').fadeOut();
                $('#alert').hide();
                $('#success').show();
            });
        });
    }

    function blob2file(blob, name, extension) {
        blob.name = name;
        blob.extension = extension;
        return blob;

    }
  
  
    function prepare_upload(file, result) {

        file_md5(file,
            function(p) {
                const a = p / 100;
                progress(25 + 25 * a, '分析文件');
                console.log(p);
            },
            function(md5) {

                progress(50, '分析完成');
                let type = file.extension;
                const filename = md5 + type;
                console.log(filename);
                file_has(filename,
                    function(has) {
                        console.log(has);
                        if (has) {
                            progress(90, '上传完成');
                            result({
                                'url': file_url(filename),
                                'filename': filename,
                                'md5': md5
                            });
                        } else {
                            file_upload(filename, md5, file,
                                function(p) {
                                    const a = p / 100;
                                    progress(50 + 40 * a, '上传数据');
                                    console.log(p);
                                },
                                function() {
                                    progress(90, '上传完成');
                                    result({
                                        'url': file_url(filename),
                                        'filename': filename,
                                        'md5': md5
                                    });
                                }
                            );

                        }
                    }
                )
            }
        );

    }

    <?php }else { ?>
        function loaded() {}
 <?php } ?>

</script>
<!-- Main content -->
<section class="content">

    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
            <!-- Info Boxes Style 2 -->


            <?php if ($model->image == null) { ?>

                <div class="info-box bg-yellow" id="alert" hidden>
                    <span class="info-box-icon"><i class="fa fa-cog fa-spin"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">资源需要进行预处理之后才能使用！</span>
                        <span class="info-box-number">预处理</span>

                        <div class="progress">
                            <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
                        </div>
                        <span class="progress-description" id="progress-description">
                            准备下载
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>

                <div class="alert alert-success alert-dismissible" id="success" hidden>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> 恭喜!</h4>
                    资源已经可以使用。
                </div>

            <?php } ?>





            <div class="box box-primary direct-chat direct-chat-primary ">

                <div class="box-header with-border">
                    <div class="box-tools pull-right">
                        资源窗口
                    </div>
                </div>
                <!-- /.box-header -->


                <div class="box-body" >
                    <?=$container?>
                </div>

            </div>

        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-5 connectedSortable">


            <div class="box  box-primary direct-chat direct-chat-primary">
                <div class="box-header">
                    <h3 class="box-title"><b>资源信息</b></h3>


                    <div class="box-tools pull-right">
                        <button value="<?= Html::encode($model->name) ?>" onclick='OpenModal(<?= $model->id ?>,  this)' type="button" class="btn  btn-box-tool">
                            <i class="fa fa-pen"></i>
                        </button>

                        <?= Html::a('<i class="fa fa-times"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-box-tool',
                            'data' => [
                                'confirm' => '确认删除此资源?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <tr>
                            <td><b>资源名</b></td>
                            <td id="mesh-name"><?= \yii\helpers\Html::encode($model->name) ?></td>
                        </tr>
                        <tr>
                            <td><b>资源类型</b></td>
                            <td id="mesh-name"><?= \yii\helpers\Html::encode($model->file->type) ?></td>
                        </tr>
                        <tr>
                            <td><b>创建者</b></td>
                            <td id="mesh-user"><?= \yii\helpers\Html::encode($model->author->username ) ?></td>
                        </tr>
                        <tr>
                            <td><b>创建时间</b></td>
                            <td id="mesh-user"><?= $model->created_at ?></td>
                        </tr>

                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </section>
        <!-- right col -->
    </div>
    <!-- /.row (main row) -->
</section>
<!-- /.content -->



<div class="modal fade" id="modal-default">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">修改信息</h4>
            </div>
            <div class="modal-body">




                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

                <div class="box-body">
                    <div class="form-group">

                        <label class="col-sm-2 control-label">模型名称</label>

                        <div class="col-sm-10">
                            <input type="hidden" id="model_id" name="id" value="1">
                            <input name="name" class="form-control" placeholder="名称" id="model_name" value="">
                        </div>
                    </div>


                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-success pull-right">提交修改</button>
                </div>
                <!-- /.box-footer -->


                <?php ActiveForm::end() ?>


            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>