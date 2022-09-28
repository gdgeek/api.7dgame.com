<?php

use yii\helpers\Html;
use app\components\FileUploadWidget;

$this->title = \Yii::t('app', '模型上传');


$this->params['breadcrumbs'][] = ['label' => 'Polygens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<section class="content">
      <!-- COLOR PALETTE -->
      <div class="box box-info color-palette-box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tag"></i> 上传面板</h3>
        </div>
        <div class="box-body">
          
        <?= FileUploadWidget::widget([
            'model'=>$model,
            'fileType'=>'.glb',
            'button'=> \Yii::t('app', 'Upload glb file'),
            'compressed'=>false,
        ]) 
        ?>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->

</section>
