<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\AFrameAsset;
use backend\assets\FileAsset;

use yii\widgets\ActiveForm;

FileAsset::register($this);
AFrameAsset::register($this);

/* @var $this yii\web\View */
/* @var $model common\models\Polygen */

$this->title = '模型展示';
$this->blocks['subTitle'] = '模型名称: ' . \yii\helpers\Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => '模型管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('
function OpenModal(id, self){
    $("#model_id").val(id);
    $("#model_name").val(self.value);
    $("#modal-default").modal();
}
',  \yii\web\View::POS_BEGIN);



if (Yii::$app->params['information']['disk']) {

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




if ($prepare) {
    $this->registerJs(
        <<<EOT
    $('#alert').show();
    $('#success').hide();
    progress(0, '读取数据');
EOT
    );
} else {
    $this->registerJs(
        <<<EOT
    $('#alert').hide();
    $('#success').hide();
EOT
    );
}




?>


<script>
    function progress(bar, description) {
        $('#progress-bar').css('width', bar + '%');
        $('#progress-description').text(description);
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
    <?php if ($prepare) { 
        ?>
        function prepare(result) {
            const canvas = document.querySelector('a-scene').components.screenshot.getCanvas('equirectangular');
            const context = canvas.getContext('2d');
            const imgData = context.getImageData(2048 + 512, 512, 1024, 1024);
            const temp = document.createElement('canvas');
            temp.width = 1024;
            temp.height = 1024;
            const tc = temp.getContext('2d');
            tc.putImageData(imgData, 0, 0);
            const type = "image/jpeg";
         //   const strDataURI = temp.toDataURL(type);
            temp.toBlob(function(blob) {
                progress(25, '处理完成，提交数据');
                console.log(blob);
                prepare_upload(blob2file(blob, '<?= \yii\helpers\Html::encode($model->name) ?>', '.jpg'), function(data) {
                    data.type = type;
                    result(data);
                });
            }, type);
        }


        function loaded(center, size) {
            prepare(function(fileData) {
                console.log(size);
                let data = {};
                data.id = <?= $model->id ?> ;
                data.image = fileData;
                let info = {size, center}
               //alert(JSON.stringify(info))
                data.info = JSON.stringify(info)
                $.post('<?= Url::toRoute(['polygen/prepare']) ?>', data, function(data) {
                    console.log(data);
                    progress(100, '处理完毕');
                    $('#loading').fadeOut();
                    $('#alert').hide();
                    $('#success').show();
                });


            });
        }

    <?php } else { 
        

        ?>
        
        function loaded(center, size) {
            let info = '<?=$model->info ?>';
            $('#loading').fadeOut();
            $('#mesh-size').text(info);
       }

    <?php } ?>



    const AFRAME = window.AFRAME;


    /*
     * Scales the object proportionally to a set value given in meters.
     */
    /**/
    AFRAME.registerComponent('natural-size', {
        schema: {
            width: {
                type: "number",
                default: undefined // meters
            },
            height: {
                type: "number",
                default: undefined // meters
            },
            depth: {
                type: "number",
                default: undefined // meters
            }
        },

        init() {
            this.loading();
            this.el.addEventListener('model-loaded', this.rescale.bind(this));
        },


        rescale() {
            progress(20, '下载完成，数据处理');
            const el = this.el;
            const data = this.data;
            const model = el.object3D;

            const box = new THREE.Box3().setFromObject(model);
            const center = box.getCenter();
            console.log(center);
            const size = box.getSize();
            if (!size.x && !size.y && !size.z) {
                return;
            }

            let scale = 1;
            if (data.width) {
                scale = data.width / size.x;
            } else if (data.height) {
                scale = data.height(size.y);
            } else if (data.depth) {
                scale = data.depth / size.z;
            }
            el.setAttribute('scale', `${scale} ${scale} ${scale}`);
            loaded(center, size);
        },
        remove() {
            this.el.removeEventListener('model-loaded', this.rescale);
        },

        loading() {

        },
    });
</script>





<!-- Main content -->
<section class="content">

    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
            <!-- Info Boxes Style 2 -->


            <?php if ($prepare) { ?>

                <div class="info-box bg-yellow" id="alert" hidden>
                    <span class="info-box-icon"><i class="fa fa-cog fa-spin"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">模型需要进行预处理之后才能使用！</span>
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

            <?php } ?>

            <div class="alert alert-success alert-dismissible" id="success" hidden>
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> 恭喜!</h4>
                模型已经可以使用。
            </div>


            <div class="box box-primary direct-chat direct-chat-primary ">

                <div class="box-header with-border">
                    <div class="box-tools pull-right">
                        模型窗口
                    </div>
                </div>
                <!-- /.box-header -->


                <div class="box-body">
                    <a-scene background="color: #E0FFFF" id="a-scene" embedded="" style="height:300px;width:100%">
                        <a-assets>
                            <a-asset-items id="model" src="<?= $model->file->url ?>"></a-asset-items>
                        </a-assets>

                        <a-entity id='cameraWrapper' position="0 -1.6 1">
                            <a-camera></a-camera>
                        </a-entity>

                        <a-entity natural-size="width:1" gltf-model="#model" position="0 0 0"> </a-entity>
                    </a-scene>
                </div>
                <!-- /.box-body -->
                <?php if ($prepare) { ?>
                    <!-- Loading (remove the following to stop the loading)-->
                    <div class="overlay" id="loading">
                        <h5><i class="fa fa-cog"></i> 模型预处理...</h5>
                        <i class="fa fa-cog fa-spin"></i>
                    </div>

                <?php } else { ?>
                    <!-- Loading (remove the following to stop the loading)-->
                    <div class="overlay" id="loading">
                        <h5><i class="fa fa-refresh"></i> 载入模型...</h5>
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>

                <?php } ?>

                <!-- end loading -->
            </div>

        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-5 connectedSortable">


            <div class="box  box-primary direct-chat direct-chat-primary">
                <div class="box-header">
                    <h3 class="box-title"><b>模型信息</b></h3>


                    <div class="box-tools pull-right">
                        <button value = "<?=Html::encode($model->name) ?>" onclick='OpenModal(<?= $model->id ?>,  this)' type="button" class="btn  btn-box-tool">
                            <i class="fa fa-pen"></i>
                        </button>

                        <?= Html::a('<i class="fa fa-times"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-box-tool',
                            'data' => [
                                'confirm' => '确认删除此模型?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <tr>
                            <td><b>模型名</b></td>
                            <td id="mesh-name"><?=  \yii\helpers\Html::encode($model->name) ?></td>
                        </tr>
                        <tr>
                            <td><b>创建者</b></td>
                            <td id="mesh-user"><?= $model->author->username ?></td>
                        </tr>
                        <tr>
                            <td><b>创建时间</b></td>
                            <td id="mesh-user"><?= $model->created_at ?></td>
                        </tr>
                        <tr>
                            <td><b>模型尺寸</b></td>
                            <td id="mesh-size"></td>
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